# Steps to Add Your Model

1. Build your data structure

What is data going to look like going in and out?

	Vote:
	- entry_id (unsigned integer, primary key)
	- upvotes (unsigned integer)
	- downvotes (unsigned integer)

2. Add your database table to the module installer
```[upd.upvotedownvote.php]
if(!ee()->db->table_exists('upvotedownvote_votes'))
{

    ee()->dbforge->add_field(
        [
            // entry_id, unsigned integer
            'entry_id'           => [
                'type'              => 'int',
                'constraint'        => 6,
                'unsigned'          => true,
            ],
            // upvotes, unsigned integer
            'upvotes'            => [
                'type'              => 'int',
                'constraint'        => 6,
                'unsigned'          => true,
            ],
            // downvotes, unsigned integer
            'downvotes'          => [
                'type'              => 'int',
                'constraint'        => 6,
                'unsigned'          => true,
            ],
        ]
    );

    ee()->dbforge->add_key('entry_id', true);

    ee()->dbforge->create_table('upvotedownvote_votes');

}
```

3. Initiate your model in your addon.setup.php
```[addon.setup.php]
	'models'            => [
        'Vote'    => 'UpvoteDownvote\\Models\\Vote',
	],
```

4. Scaffold our model
```[Models/Vote.php]
<?php

namespace UpvoteDownvote\Models;

use EllisLab\ExpressionEngine\Service\Model\Model;

class Vote extends Model {

    // Documentation: https://docs.expressionengine.com/latest/development/services/model/building-your-own.html
    // You can get this model by using:
    // ee('Model')->get('upvotedownvote:Vote');

    protected static $_primary_key = 'entry_id';

    protected static $_table_name = 'upvotedownvote_votes';

    // Add your properties as protected variables here
    protected $upvotes;
    protected $downvotes;

}
```

5. Access our model in the module

See `mod.upvotedownvote.php`

6. Add a Relationship
```[addon.setup.php]
'models.dependencies' => [
    'Vote'   => [
        'ee:ChannelEntry'
    ]
],
```

```[Models/Vote.php]
protected static $_relationships = [
    'ChannelEntry'  => [
        'type'      => 'HasOne',
        'model'     => 'ee:ChannelEntry',
        'from_key'  => 'entry_id',
        'to_key'    => 'entry_id',
    ],
];
```

Check out `top_votes` function in `mod.upvotedownvote.php` to see it in action.