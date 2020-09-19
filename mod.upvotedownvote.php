<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use EllisLab\ExpressionEngine\Library\Data\Collection;

class Upvotedownvote {

	// 5. Access our model in the module
	// cast_vote and votes functions both access and update models
	public function cast_vote()
	{

		// Let's get the entry vote
		$entryId = ee()->input->get_post('entry_id');
		$type = ee()->input->get_post('type');

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
			$votes->upvotes += 1;
		}

		if($type == 'down') {
			$votes->downvotes += 1;
		}

		$votes->save();

		$vars = [
			'upvotes'		=> $votes->upvotes,
			'downvotes'		=> $votes->downvotes,
			'sentiment'		=> $this->getSentiment($votes),
			'percentage'	=> $this->getPercentage($votes),
		];

		return ee()->output->send_ajax_response($vars);

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
			'downvotes'	=> $votes->downvotes,
			'sentiment'	=> $this->getSentiment($votes),
		];

		return ee()->TMPL->parse_variables_row($tagData, $vars);

	}

	private function getSentiment($votes)
	{

		if($votes->upvotes == $votes->downvotes) {
			return 'neutral';
		}

		if($votes->upvotes > $votes->downvotes) {
			return 'positive';
		}

		return 'negative';

	}

	private function getPercentage($votes)
	{
		if($votes->upvotes === 0 && $votes->downvotes == 0) {
			return 0;
		}

		if($votes->upvotes > $votes->downvotes) {
			return $votes->downvotes / ($votes->upvotes + $votes->downvotes);
		}

		return round(($votes->upvotes / ($votes->upvotes + $votes->downvotes)) * 100, 2);
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
		$votes = ee('Model')
					->get('upvotedownvote:Vote')
					->with('ChannelEntry')
					->filter('ChannelEntry.channel_id', $channel->channel_id)
					->order('upvotes', 'DESC')
					->all();

		// We can get the entries too
		// $entries = ee('Model')
		// 			->get('ChannelEntry')
		// 			// To order or filter on a relationship, you have to eager load it
		// 			->with('upvotedownvote:Votes')
		// 			->filter('channel_id', $channel->channel_id)
		// 			->order('upvotedownvote:Votes.upvotes', 'DESC')
		// 			->limit($limit)
		// 			->all();
		$output = [];

		foreach ($votes as $vote) {

			// Then we'll parse to for the templates
			$output[] = [
				'entry_id'      => $vote->ChannelEntry->entry_id,
				'title'         => $vote->ChannelEntry->title,
				'url_title'     => $vote->ChannelEntry->urltitle,
				'entry_date'    => $vote->ChannelEntry->entry_date,
				'upvotes'       => $vote->upvotes,
				'downvotes'     => $vote->downvotes,
				'percentage'    => ($vote->upvotes + $vote->downvotes == 0) ? 0 : $vote->upvotes / ($vote->upvotes + $vote->downvotes),
			];

		}

		$tagData = ee()->TMPL->tagdata;

		return ee()->TMPL->parse_variables($tagData, $output);

	}

	// We need this for our javascript
	public function get_action_url()
	{

		$actionId = ee()->functions->fetch_action_id(__CLASS__, 'cast_vote');

		$siteUrl = ee()->functions->fetch_site_index();

		return "{$siteUrl}?ACT={$actionId}";

	}

}