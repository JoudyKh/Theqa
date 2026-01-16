<?php

namespace App\Services\General\Section;

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Traits\ConfigTrait;
use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ExamResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TeacherResource;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Http\Requests\Api\General\Section\GetAllSectionRequest;

class SectionService
{
    use ConfigTrait;
    public function __construct()
    {
        $this->initConfig();
    }
    public function getAll(Section &$parentSection = null, GetAllSectionRequest &$request)
    {
        $sectionsType = request()->query('type', Constants::SECTION_TYPE_SUPER);
        $config = $this->getLayerConfig($sectionsType, false);


        $extraData = [];
        $currUser = User::with(['roles', 'studentSectionsPivotTable'])->where('id', auth('sanctum')->id())->first();
        $sections = Section::with($config['with']['relations'])
            ->where('sections.type', request()->query('type', Constants::SECTION_TYPE_SUPER));
        foreach ($config['with']['functions'] as $fun) {
            Section::$fun($sections);
        }
        if (isset($config['with']['scopeFunctions']))
            foreach ($config['with']['scopeFunctions'] as $fun) {
                $sections->$fun();
            }
        $sections->orderBy(key($config['order_by']), $request->boolean('latest') ? 'desc' : current($config['order_by']));
        if ($parentSection?->id) {
            $sections->where("sections.parent_id", $parentSection->id);
        }
        if ($request->search) {
            $sections->where('sections.name', 'LIKE', '%' . trim(strtolower($request->search)) . '%');
        }
        if ($request->has('teacher_id')) {
            $sections->whereHas('teachers', function ($q) use (&$request) {
                $q->where('users.id', $request->input('teacher_id'));
            });
            $extraData['teacher'] = TeacherResource::make(User::with(['images', 'roles'])->where('id', $request->input('teacher_id'))->firstOrFail());
        } elseif ($request->has('student_id')) {
            $sections->whereHas('students', function ($users) use (&$request) {
                $users->where('users.id', $request->input('student_id'));
            });
            $extraData['student'] = StudentResource::make(User::with(['images', 'roles'])->where('id', $request->input('student_id'))->firstOrFail());
        }

        if ($parentSection?->id) {
            $parentSection->load([
                'teachers',
                'parentSection.parentSection',
            ]);
            if ($currUser) {
                $parentSection->load([
                    'sectionStudents' => function ($query) use ($currUser) {
                        $query->where('student_id', $currUser?->id);
                    }
                ]);
            }


            $extraData['parent_section'] = null;
            if ($parentSection->type == Constants::SECTION_TYPE_COURSES) {
                $extraData['parent_section'] = CourseResource::make($parentSection);
            } else {
                $extraData['parent_section'] = SectionResource::make($parentSection);
            }
        }



        if ($currUser and $currUser->hasRole(Constants::STUDENT_ROLE)) {

            $student_courses_ids = Section::mergeSubscribed($currUser);
            app()->instance('student_courses_ids', $student_courses_ids);

            if ($request->mine) {
                $sections->whereHas('students', function ($query) use ($currUser) {
                    $query->where('id', $currUser->id);
                });
            }


        }

        if ($request->boolean('subSections')) {
            $sections->with(['subSections']);
        }

        switch (request()->query('type', $parentSection?->type == Constants::SECTION_TYPE_SUPER ? Constants::SECTION_TYPE_COURSES : null)) {
            case Constants::SECTION_TYPE_COURSES:
                $resourceClass = 'App\Http\Resources\Section\CourseResource';
                break;
            default:
                $resourceClass = 'App\Http\Resources\Section\SectionResource';
        }

        $sections = $request->boolean('get') ? $sections->get() : $sections->paginate(config("app.pagination_limit"));

        return success(
            $resourceClass::collection($sections),
            200,
            $extraData,
        );
    }

    public function show(Section $section)
    {
        $config = $this->getLayerConfig($section->type, true);
        $section = Section::with($config['with']['relations'])->find($section->id);
        foreach ($config['with']['functions'] as $fun) {
            Section::$fun($section);
        }
        $relations = [];
        if (isset($config['with']['conditionalRelations']))
            foreach ($config['with']['conditionalRelations'] as $cRel) {
                $queryString = substr($cRel, strpos($cRel, '?') + 1);
                $parameters = [];
                parse_str($queryString, $parameters);
                $pos = strpos($cRel, '?');
                if ($pos !== false) {
                    $relation = substr($cRel, 0, $pos);
                }
                $loadRelation = true;
                foreach ($parameters as $parameter => $value) {
                    if ($section->$parameter != $value) {
                        $loadRelation = false;
                        break;
                    }
                }
                if ($loadRelation) {
                    $relations[] = $relation;
                }
            }
        if (count($relations) > 0) {
            $section->load($relations);
        }

        if ($section->type == Constants::SECTION_TYPE_COURSES) {
            return success(CourseResource::make($section));
        }
        return success(SectionResource::make($section));
    }
}
