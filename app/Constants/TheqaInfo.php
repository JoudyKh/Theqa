<?php

namespace App\Constants;
use App\Models\User;
use App\Models\Offer;
use App\Models\Slider;
use App\Models\Section;
use App\Models\SliderImage;
use App\Models\StudentAnswer;
use Illuminate\Validation\Rule;
use App\Http\Resources\OfferResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\Section\SectionResource;

/**
 * @OA\Schema(
 *     schema="UpdateInfoRequest",
 *     type="object",
 *     title="Update Info Request",
 *     description="Request body for updating information",
 *
 *     @OA\Property(property="hero-title1", type="string", description="Hero description", nullable=true),
 *     @OA\Property(property="hero-title2", type="string", description="Hero description", nullable=true),
 *     @OA\Property(property="hero-description1", type="string", description="Hero description", nullable=true),
 *     @OA\Property(property="hero-description2", type="string", description="Hero description", nullable=true),
 *
 *     @OA\Property(property="about_us-description", type="string", description="About us description", nullable=true),
 *     @OA\Property(property="about_us-image", type="string", format="binary", description="About us image", nullable=true),
 *     @OA\Property(property="about_us-video", type="string", format="binary", description="About us video", nullable=true),
 *
 *     @OA\Property(property="about_platform-description", type="string", description="About platform description", nullable=true),
 *     @OA\Property(property="about_platform-image1", type="string", format="binary", description="First image of the about platform", nullable=true),
 *     @OA\Property(property="about_platform-image2", type="string", format="binary", description="Second image of the about platform", nullable=true),
 *     @OA\Property(property="about_platform-image3", type="string", format="binary", description="Third image of the about platform", nullable=true),
 *
 *     @OA\Property(property="application-google_play", type="string", description="Application Google Play link", nullable=true , example="https://www.google.com"),
 *
 *     @OA\Property(property="owner-name", type="string", description="Owner name", nullable=true),
 *     @OA\Property(property="owner-description", type="string", description="Owner description", nullable=true),
 *     @OA\Property(property="owner-image", type="string",format="binary", description="Owner image", nullable=true),
 *
 *     @OA\Property(property="contact_us-email", type="string", format="email", description="Contact email", nullable=true),
 *     @OA\Property(property="contact_us-phone", type="string", description="Contact phone number", nullable=true),
 *
 *     @OA\Property(property="social_media-whatsapp", type="string", description="WhatsApp link", nullable=true),
 *     @OA\Property(property="social_media-instagram", type="string", format="url", description="Instagram link", nullable=true , example="https://www.google.com"),
 *     @OA\Property(property="social_media-facebook", type="string", format="url", description="Facebook link", nullable=true , example="https://www.google.com"),
 *     @OA\Property(property="social_media-web", type="string", format="url", description="Web link", nullable=true , example="https://www.google.com")
 * )
 *
 * @OA\Post(
 *     path="/admin/infos/update",
 *     operationId="post-update-info",
 *     tags={"Admin", "Admin - Info"},
 *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Site information data",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/UpdateInfoRequest"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid.")
 *         )
 *     )
 * )
 */

class TheqaInfo
{

    /**
     * @INFO
     * @var array
     **/
    public static $infos = [
        'hero' => [
            'title1' => 'hero title1',
            'title2' => 'hero title2',
            'description1' => 'hero description1',
            'description2' => 'hero description2',
        ],

        'about_us' => [
            'description' => 'about us description',
            'image' => 'SiteFiles/image1.jpg',
            'video' => 'SiteFiles/video1.mp4',
        ],

        'about_platform' => [
            'description' => 'about platform description',
            'image1' => 'SiteFiles/image5.jpg',
            'image2' => 'SiteFiles/image6.jpg',
            'image3' => 'SiteFiles/image7.jpg',
        ],

        'application' => [
            'google_play' => 'https://www.google.com',
        ],

        'owner' => [
            'name' => 'saad',
            'description' => 'owner description',
            'image' => 'SiteFiles/image8.jpg'
        ],

        'contact_us' => [
            'email' => 'email@gmail.com',
            'phone' => '+0987654321',
        ],

        'social_media' => [
            'whatsapp' => 'whatsapp',
            'instagram' => 'https://www.instagram.com',
            'facebook' => 'https://www.facebook.com',
            'web' => 'https://www.facebook.com',
        ],
    ];

    /**
     * @RULES
     * @var array
     **/
    public static $rules =
        [
            'hero-title1' => ['nullable', 'string'],
            'hero-title2' => ['nullable', 'string'],
            'hero-description1' => ['nullable', 'string'],
            'hero-description2' => ['nullable', 'string'],

            'about_us-description' => ['nullable', 'string'],
            'about_us-image' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'about_us-video' => ['nullable', 'file', 'mimes:mp4'],

            'about_platform-description' => ['nullable', 'string'],
            'about_platform-image1' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'about_platform-image2' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'about_platform-image3' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],

            'application-google_play' => ['nullable', 'string', 'url'],

            'owner-name' => ['nullable', 'string'],
            'owner-description' => ['nullable', 'string'],
            'owner-image' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],

            'contact_us-email' => ['nullable', 'string', 'email'],
            'contact_us-phone' => ['nullable', 'string'],

            'social_media-whatsapp' => ['nullable', 'string'],
            'social_media-instagram' => ['nullable', 'string', 'url'],
            'social_media-facebook' => ['nullable', 'string', 'url'],
            'social_media-web' => ['nullable', 'string', 'url'],
        ];


    public static function getValidationRules(): array
    {
        return [
            'signup' => [
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'username')->whereNull('deleted_at'),
                ],
                'email' => [
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->whereNull('deleted_at'),
                ],
                'location' => 'string|max:255',
                'birth_date' => 'string|date_format:Y-m-d',
                'full_name' => ['required', 'string', 'min:5', 'max:255'],
                'mid_name' => 'string|max:255',
                'phone_number' => 'required|string|min:8|max:20',
                'password' => 'required|string|min:8',
                'image' => 'image|mimes:png,jpg,jpeg',
                'fcm_token' => 'string',
            ],
        ];
    }

    /**
     * @IMAGE-KEYS
     * @var array
     */
    public static $imageKeys = [
        'about_us-image',
        'about_platform-image1',
        'about_platform-image2',
        'about_platform-image3',
        'owner-image',
    ];

    /**
     * @VEDIOS-KEYS
     * @var array
     */
    public static $videoKeys = [
        'about_us-video',
    ];

    /**
     * @FILE-KEYS
     * @var array
     */
    public static $fileKeys = [
    ];

    /**
     * @TRANSLATION-KEYS
     * @var array
     */
    public static array $translatableKeys = [];
    /**
     * @COMMA-SEPARATED-KEYS
     * @var array
     */
    public static array $commaSepratadKeys = [];
    public static function home(&$data = [])
    {
        $supers = Section::where('type', Constants::SECTION_TYPE_SUPER)->inRandomOrder()->limit(10)->get();

        $sliders = Slider::whereIn('type', Slider::$homeTypes)->get();

        $groupedSliders = $sliders->groupBy('type');

        $sliders = [
            'hero' => SliderResource::collection($groupedSliders->get(Slider::HERO_TYPE, [])),
            'locations' => SliderResource::collection($groupedSliders->get(Slider::LOCATIONS, [])),
            'our_features' => SliderResource::collection($groupedSliders->get(Slider::OUR_FREATURES_TYPE, [])),
        ];

        $data['sliders'] = $sliders ?? [];
        $data['sections']['data'] = SectionResource::collection($supers);
        return $data;
    }

    public static function mobileHome(&$data = [])
    {
        $studentId = auth('sanctum')->id();

        $supers = Section::where('type', 'super')->latest()->limit(4)->get();
        $offers = Offer::latest()->limit(5)->get();

        $top_students = User::with(['images'])
            ->whereHas('roles', function ($role) {
                $role->where('name', Constants::STUDENT_ROLE);
            })
            ->withTopStudentsQuery(
                [
                    'solved_exams_count' => 'desc',
                    'total_avg' => 'desc',
                    'attempts_count_sum' => 'asc',
                ],
                'ASC',
                false
            )
            ->limit(3)
            ->get();

        Section::mergeSubscribed();

        $data['sections']['data'] = SectionResource::collection($supers);
        $data['offers']['data'] = OfferResource::collection($offers);
        $data['top_students']['data'] = StudentResource::collection($top_students);

        return $data;
    }
}
