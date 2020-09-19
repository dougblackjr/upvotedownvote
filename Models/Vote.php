<?php

namespace UpvoteDownvote\Models;

use EllisLab\ExpressionEngine\Service\Model\Model;

class Vote extends Model {

    // 4. Scaffold our model
    // Documentation: https://docs.expressionengine.com/latest/development/services/model/building-your-own.html
    // You can get this model by using:
    // ee('Model')->get('upvotedownvote:Vote');

    // Primary Key: This is entry_id in our model.
    protected static $_primary_key = 'id';

    // Table Name: Our database table where our data resides
    protected static $_table_name = 'upvotedownvote_votes';

    // Our properties as protected variables
    // Each of these can be accessed by
    // $vote->upvotes and $vote->downvotes
    protected $id;
    protected $entry_id;
    protected $upvotes;
    protected $downvotes;

    // 6. Adding a relationship
    // Here we define the relationship
    protected static $_relationships = [
        'ChannelEntry'  => [
            'type'      => 'HasOne',
            'model'     => 'ee:ChannelEntry',
            'from_key'  => 'entry_id',
            'to_key'    => 'entry_id',
            'inverse' => [
                'name' => 'Vote',
                'type' => 'BelongsTo'
            ]
        ],
    ];

}