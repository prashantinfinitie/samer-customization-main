<?php defined('BASEPATH') or exit('No direct script access allowed');



class Migration_add_shipping_company_id_to_fund_transfers extends CI_Migration
{

        public function up(){

            $fields = [
                'shipping_company_id' =>[
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' =>  true,
                    'default' => null,
                    'after' => 'delivery_boy_id'
                ]
                ];

                $this->dbforge->add_column('fund_transfers',$fields);

        }

        public function down(){

            $this->dbforge->drop_column('fund_transfers','shipping_company_id');

        }
}
