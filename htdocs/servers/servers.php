<?php
/*
	servers/servers.php

	access:
		namedadmins

	Interface to view and manage what name servers are managed by this interface. The main reason
	for this interface is to put a view onto what is being recorded to allow the API to function and
	make it easier to get reports on a per-server basis.
*/


class page_output
{
	var $obj_table;


	function check_permissions()
	{
		return user_permissions_get("namedadmins");
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	function execute()
	{
		// establish a new table object
		$this->obj_table = New table;

		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "name_servers";

		// define all the columns and structure
		$this->obj_table->add_column("bool_tick", "server_primary", "");
		$this->obj_table->add_column("standard", "server_name", "");
		$this->obj_table->add_column("standard", "server_description", "");
		$this->obj_table->add_column("standard", "server_type", "");
		$this->obj_table->add_column("standard", "sync_status", "NONE");

		// defaults
		$this->obj_table->columns		= array("server_primary", "server_name", "server_description", "server_type", "sync_status");
		$this->obj_table->columns_order		= array("server_name");
		$this->obj_table->columns_order_options	= array("server_name");

		$this->obj_table->sql_obj->prepare_sql_settable("name_servers");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "");
		$this->obj_table->sql_obj->prepare_sql_addfield("api_sync_config", "");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();


		// check sync status
		$sync_status_config = sql_get_singlevalue("SELECT value FROM config WHERE name='SYNC_STATUS_CONFIG'");

		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			if ($this->obj_table->data[$i]["server_type"] == "api")
			{
				if ($sync_status_config != $this->obj_table->data[$i]["api_sync_config"])
				{
					$this->obj_table->data[$i]["sync_status"]	= "<span class=\"table_highlight_important\">". lang_trans("status_unsynced") ."</span>";
				}
				else
				{
					$this->obj_table->data[$i]["sync_status"]	= "<span class=\"table_highlight_open\">". lang_trans("status_synced") ."</span>";
				}
			}
			else
			{
				$this->obj_table->data[$i]["sync_status"]		= "<span class=\"table_highlight_open\">". lang_trans("status_databasesync") ."</span>";
			}
		}

	}


	function render_html()
	{
		// title + summary
		print "<h3>NAME SERVERS</h3>";
		print "<p>Define all the name servers that are being used for management in this interface, all NS servers should be set here since the values are used to set the NS records on the domains.</p>";

		// table data
		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>There are currently no name servers being managed.</p>");
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_details", "servers/view.php", $structure);

			// logging link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_logs", "servers/logs.php", $structure);

			// delete link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_delete", "servers/delete.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		print "<p><a class=\"button\" href=\"index.php?page=servers/add.php\">Add New Server</a></p>";

	}

}


?>