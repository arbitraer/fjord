<?php

return [
    'route_prefix' => 'main',
    'form_fields' => [
        [
            'translatable' => false,
            'id' => 'title',
            'type' => 'input',
            'title' => 'Title',
            'placeholder' => config('app.name'),
            'hint' => 'The Title of this Website.',
            'width' => 12
        ],
    ]
];
