<?php

return [
    'author'            => 'tripleNERDscore',
    'author_url'        => 'https://triplenerdscore.net',
    'name'              => 'UpvoteDownvote',
    'description'       => 'Register upvotes and downvotes on your content',
    'version'           => '1.0.0',
    'namespace'         => 'UpvoteDownvote',
    'settings_exist'    => false,
    // Advanced settings
    // STEP 3. Initiate your model in your addon.setup.php
    'models'            => [
        'Vote'    => 'Models\Vote',
	],
    // Step 6. Add our relationship
    // Instead of hacking the core, we define the inverse of our relationship here
    'models.dependencies' => [
        'Vote'   => [
            'ee:ChannelEntry'
        ]
    ],
];