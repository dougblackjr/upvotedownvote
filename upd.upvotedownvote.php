<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upvotedownvote_upd {

    public $version = '1.0.0';

    public function install()
    {

        ee()->load->dbforge();

        $data = array(
            'module_name'           => 'Upvotedownvote',
            'module_version'        => $this->version,
            'has_cp_backend'        => 'n',
            'has_publish_fields'    => 'n'
        );

        ee()->db->insert('modules', $data);

        // Create an action for voting in browser
        // NOTE: We want CSRF protection, so we leave that blank
        $data = [
            'class'         => 'Upvotedownvote',
            'method'        => 'cast_vote',
        ];

        ee()->db->insert('actions', $data);

        // Create our vote tables
        // 1. Build your data structure
        // 2. Add your database table to the module installer
        if(!ee()->db->table_exists('upvotedownvote_votes'))
        {

            ee()->dbforge->add_field(
                [
                    // id, our primary key
                    'id'           => [
                        'type'              => 'int',
                        'constraint'        => 6,
                        'unsigned'          => true,
                        'auto_increment'    => true,
                    ],
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

            ee()->dbforge->add_key('id', true);

            ee()->dbforge->create_table('upvotedownvote_votes');

        }

    }

    public function update($current = '')
    {

        return true;

    }

    public function uninstall()
    {

        // Delete modules
        ee()->db->where('module_name', 'Upvotedownvote');

        ee()->db->delete('modules');

        // Delete actions
        ee()->db->where('class', 'Upvotedownvote');

        ee()->db->delete('actions');

        // We won't uninstall our table, just in case you want the votes
        // For later. But, if you want to see what that looks like...
        // $tablePrefix = ee()->db->dbprefix;
        // ee()->db->query("DROP TABLE IF EXISTS {$tablePrefix}upvotedownvote_votes");

    }

}