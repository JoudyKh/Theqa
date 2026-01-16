<?php

namespace App\Constants;

class Constants
{
    const SUPER_ADMIN_ROLE = 'super_admin';
    const ADMIN_ROLE = 'admin';
    const STUDENT_ROLE = 'student';
    const TEACHER_ROLE = 'teacher';
    const ROLES_ARRAY = [self::ADMIN_ROLE , self::STUDENT_ROLE , self::TEACHER_ROLE] ;
    const MALE_GENDER = 'MALE';
    const FEMALE_GENDER = 'FEMALE';

    const PARENTS = ['book_section' , 'super'] ;
    const CHILDREN_OF = [
        'super' => ['courses'] ,
        'courses' => ['course_sections'] ,
        'course_sections' => [] ,
        'book_section' => ['book_sub_section'] ,
        'book_sub_section' => [] ,
    ] ;

    //to avoid spelling errors
    const SECTION_TYPE_SUPER = 'super' ;
    const SECTION_TYPE_COURSES = 'courses' ;
    const SECTION_TYPE_COURSE_SECTIONS = 'course_sections' ;
    const SECTION_TYPE_BOOK_SECTION = 'book_section' ;
    const SECTION_TYPE_BOOK_SUB_SECTION = 'book_sub_section' ;

    const SECTIONS_TYPES = [
        'super' => [
            'attributes' => [
                'name',
                'image',
                'description',
            ],
            'rules' =>
            [
                'create' => [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'image' => 'required|mimes:jpeg,png,jpg'
                ],
                'update' => [
                    'name' => 'string|max:255',
                    'description' => 'string',
                    'image' => 'mimes:jpeg,png,jpg'
                ]
            ]

        ],

        self::SECTION_TYPE_COURSES => [
            'attributes' => [
                'name',
                'image',
                'is_free',
                'price',
                'discount',
                'description',
                'intro_video',
                'is_special'
            ],
            'rules' =>
            [
                'create' => [
                    'name' => 'required|string|max:255',
                    'image' => 'required|mimes:jpeg,png,jpg',
                    'intro_video' => 'file|mimes:mp4',
                    'is_free' => 'required|boolean',
                    'description' => 'required|string',
                    'price' => 'numeric',
                    'discount' => 'numeric',
                    'is_special' => ['boolean'],
                ],
                'update' => [
                    'name' => 'string|max:255',
                    'image' => 'mimes:jpeg,png,jpg',
                    'is_free' => 'boolean',
                    'description' => 'string',
                    'intro_video' => 'nullable|file|mimes:mp4',
                    //new
                    'is_special' => ['boolean'],
                ],
            ]

        ],

        'course_sections' => [
            'attributes' => [
                'name',
                'image',
                'description',
            ],
            'rules' =>
            [
                'create' => [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'image' => 'required|mimes:jpeg,png,jpg'
                ],
                'update' => [
                    'name' => 'string|max:255',
                    'description' => 'string',
                    'image' => 'mimes:jpeg,png,jpg'
                ]
            ]
        ],
        'book_section' => [
            'attributes' => [
                'name',
                'image',
                'description',
            ],
            'rules' =>
            [
                'create' => [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'image' => 'required|mimes:jpeg,png,jpg'
                ],
                'update' => [
                    'name' => 'string|max:255',
                    'description' => 'string',
                    'image' => 'mimes:jpeg,png,jpg'
                ]
            ]
        ],
        'book_sub_section' => [
            'attributes' => [
                'name',
                'image',
                'description',
            ],
            'rules' =>
            [
                'create' => [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'image' => 'required|mimes:jpeg,png,jpg'
                ],
                'update' => [
                    'name' => 'string|max:255',
                    'description' => 'string',
                    'image' => 'mimes:jpeg,png,jpg'
                ]
            ]
        ]
    ];
}
