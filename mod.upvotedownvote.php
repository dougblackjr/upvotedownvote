<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upvotedownvote {

    // 5. Access our model in the module
    // cast_vote and votes functions both access and update models
    public function cast_vote()
    {

    	// Let's get the entry vote
		$entryId = ee()->input->get_post('entry_id');
		$type = ee()->input->get_post('type');
		// +1 or -1
		$vote = ee()->input->get_post('vote');

		// 5. Access our model in the module
		// First, we'll try and get the votes
		$votes = ee('Model')->get('upvotedownvote:Vote')
							->filter('entry_id', $entryId)
							->first();

		// If it doesn't exist, we'll create it, and set the entry_id
		if( ! $votes ) {
			$votes = ee('Model')->make(
				// Our model name
				'upvotedownvote:Vote',
				// our default values
				[
					'entry_id'	=> $entryId,
					'upvotes'	=> 0,
					'downvotes'	=> 0,
				]
			);

		}

		// Increment the vote
		if($type == 'up') {
			$votes->upvotes += $vote;
		}

		if($type == 'down') {
			$votes->downvotes += $vote;
		}

		$votes->save();

		return ee()->output->send_ajax_response($votes);

    }

    public function votes()
    {

    	$entryId = ee()->TMPL->fetch_param('entry_id');

    	// 5. Access our model in the module
    	// First, we'll try and get the votes
    	$votes = ee('Model')->get('upvotedownvote:Vote')
    						->filter('entry_id', $entryId)
    						->first();

    	// If it doesn't exist, we'll create it, and set the entry_id
    	if( ! $votes ) {
    		$votes = ee('Model')->make(
    			// Our model name
    			'upvotedownvote:Vote',
    			// our default values
    			[
    				'entry_id'	=> $entryId,
    				'upvotes'	=> 0,
    				'downvotes'	=> 0,
    			]
    		);

    		$votes->save();
    	}

    	// Now we'll create our template params and ship it!
    	$tagData = ee()->TMPL->tagdata;

    	$vars = [
    		'upvotes'		=> $votes->upvotes,
    		'downvotes'		=> $votes->downvotes,
    		'percentage'	=> floor($votes->upvotes / $votes->downvotes),
    		'sentiment'		=> $votes->upvotes > $votes->downvotes ? 'positive' : 'negative',
    	];

    	return ee()->TMPL->parse_variables($tagdata, $vars);

    }

    // 6. Add a Relationship
    // In this function, we'll get the top voted entries from a channel
    public function top_votes()
    {

    	$channelName = ee()->TMPL->fetch_param('channel');
    	$limit = ee()->TMPL->fetch_param('limit', 50);

        $channel = ee('Model')->get('Channel')
                            ->filter('channel_name', $channelName)
                            ->first();

    	// This gets the actual votes
    	// $votes = ee('Model')->get('upvotedownvote:Vote')
    	// 					->filter('ee:ChannelEntry.channel_id', $channelId)
    	// 					->first();

    	// But lets get the entries instead
        $entries = ee('Model')
                    ->get('ChannelEntry')
                    // To order or filter on a relationship, you have to eager load it
                    ->with('upvotedownvote:Votes')
                    ->filter('channel_id', $channel->channel_id)
					->order('upvotedownvote:Votes.upvotes', 'DESC')
					->limit($limit)
					->all();

        // Then we'll parse the variables
        $output = [];

        foreach ($entries as $entry) {

            // If our entry doesn't have votes with it, we'll create and attach
            if( ! $entry->Votes ) {

                $votes = ee('Model')->make(
                    // Our model name
                    'upvotedownvote:Vote',
                    // our default values
                    [
                        'entry_id'  => $entry->entry_id,
                        'upvotes'   => 0,
                        'downvotes' => 0,
                    ]
                );

                $votes->save();

                $entry->Votes = $votes;
                $entry->save();

            }

            // Then we'll parse to for the templates
            $output[] = [
                'entry_id'      => $entry->entry_id,
                'title'         => $entry->title,
                'url_title'     => $entry->urltitle,
                'entry_date'    => $entry->entry_date,
                'author'        => $entry->Author->username,
                'upvotes'       => $entry->Votes->upvotes,
                'downvotes'     => $entry->Votes->downvotes,
            ];
        }

    	$tagData = ee()->TMPL->tagdata;

    	return ee()->TMPL->parse_variables($tagData, $output);

    }

}