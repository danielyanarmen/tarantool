<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profitability_Product_Group_Brewing extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library("jedox");
        $this->load->library("jedoxapi");
	}
	
	public function index()
	{
		$pagename = "proEO Profitability by Product Group";
		$oneliner = "One-liner here for Profitability by Product Group";
		$user_details = $this->session->userdata('jedox_user_details');
		if($this->session->userdata('jedox_sid') == '' || $this->session->userdata('jedox_sid') == NULL)
		{
			$this->session->set_userdata('jedox_referer', current_url());
			redirect("/login/page");
		}
		else if($this->jedox->page_permission($user_details['group_names'], "profitability") == FALSE)
		{
			echo "Sorry, you have no permission to access this area.";
		}
		else
		{
			if(isset($_GET['trace']) && $_GET['trace'] == TRUE){
				// Profile the page, usefull for debugging and page optimization. Comment this line out on production or just set to FALSE.
				$this->output->enable_profiler(TRUE);
				$this->jedoxapi->set_tracer(TRUE);
        	}
			else
			{
				$this->jedoxapi->set_tracer(FALSE);
			}
			
			// Initialize variables //
            $database_name = $this->session->userdata('jedox_db');
            // Comma delimited cubenames to load. Cube names with #_ prefix are aliases cubes. No spaces.
            $cube_names = "Margin_Report,#_Version,#_Month,#_Margin_Value,#_Product,#_Year";
			
			// Initialize post data //
            $year = $this->input->post("year");
            $month = $this->input->post("month");
            $product = $this->input->post("product");
			
            
            // Login. need to relogin to prevent timeout 
            $server_login = $this->jedoxapi->server_login($this->session->userdata('jedox_user'), $this->session->userdata('jedox_pass'));
            
            // Get Database
            $server_database_list = $this->jedoxapi->server_databases();
            $server_database = $this->jedoxapi->server_databases_select($server_database_list, $database_name);
            
            // Get Cubes
            $database_cubes = $this->jedoxapi->database_cubes($server_database['database'], 1,0,1);
            
            // Dynamically load selected cubes based on names
            $cube_multiload = $this->jedoxapi->cube_multiload($server_database['database'], $database_cubes, $cube_names);
			
			// Get Dimensions ids.
            $margin_report_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "Margin_Report");
			
			$margin_report_cube_info = $this->jedoxapi->get_cube_data($database_cubes, "Margin_Report");
			
			//////////////////////////// 
            // Get Dimension elements //
            ////////////////////////////
            
			// VERSION //
            // Get dimension of version
            $version_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_report_dimension_id[0]);
            // Get cube data of version alias
            $version_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Version");
            $version_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Version");
            // Export cells of version alias
            $version_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $version_dimension_id[0]);
			$version_alias_name_id = $this->jedoxapi->get_area($version_alias_elements, "Name");
            $cells_version_alias = $this->jedoxapi->cell_export($server_database['database'],$version_alias_info['cube'],10000,"",$version_alias_name_id.",*");
			
			// YEAR //
			// Get dimension of year
			//$year_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_report_dimension_id[1]);
            $year_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_report_dimension_id[1]);
            $year_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Year");
            $year_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Year");
			$year_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $year_dimension_id[0]);
			$year_alias_name_id = $this->jedoxapi->get_area($year_alias_elements, "Name");
            $cells_year_alias = $this->jedoxapi->cell_export($server_database['database'],$year_alias_info['cube'],10000,"",$year_alias_name_id.",*");
            
			// MONTH //
            // Get dimension of month
            $month_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_report_dimension_id[2]);
            // Get cube data of month alias
            $month_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Month");
            $month_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Month");
            // Export cells of month alias
            $month_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $month_dimension_id[0]);
			$month_alias_name_id = $this->jedoxapi->get_area($month_alias_elements, "Name");
            $cells_month_alias = $this->jedoxapi->cell_export($server_database['database'],$month_alias_info['cube'],10000,"", $month_alias_name_id.",*");
			
			// Margin_Value //
            // Get dimension of Margin_Value
            $margin_value_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_report_dimension_id[3]);
            // Get cube data of Margin_Value alias
            $margin_value_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Margin_Value");
            $margin_value_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Margin_Value");
            // Export cells of Margin_Value alias
            $margin_value_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_value_dimension_id[0]);
			$margin_value_alias_name_id = $this->jedoxapi->get_area($margin_value_alias_elements, "Name");
            $cells_margin_value_alias = $this->jedoxapi->cell_export($server_database['database'],$margin_value_alias_info['cube'],10000,"", $margin_value_alias_name_id.",*");
			
			// product //
            // Get dimension of product
            $product_elements = $this->jedoxapi->dimension_elements($server_database['database'], $margin_report_dimension_id[4]);
            // Get cube data of product alias
            $product_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Product");
            $product_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Product");
            // Export cells of product alias
            $product_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $product_dimension_id[0]);
			$product_alias_name_id = $this->jedoxapi->get_area($product_alias_elements, "Name");
            $cells_product_alias = $this->jedoxapi->cell_export($server_database['database'],$product_alias_info['cube'],10000,"", $product_alias_name_id.",*");
			
			// FORM DATA //
            $form_year = $this->jedoxapi->array_element_filter($year_elements, "YA"); // all years
			$form_year = $this->jedoxapi->set_alias($form_year, $cells_year_alias);
            
            $form_months = $this->jedoxapi->array_element_filter($month_elements, "MA"); // All Months
            $form_months = $this->jedoxapi->set_alias($form_months, $cells_month_alias); // Set aliases
            
            $form_product = $this->jedoxapi->array_element_filter($product_elements, "FP");
			$form_product = $this->jedoxapi->set_alias($form_product, $cells_product_alias);
			
			// form string fix //
			$temp = array();
			foreach ($form_product as $row) {
				$row['name_element'] = trim($row['name_element']);
				$temp[] = $row;
			}
			$form_product = $temp;
			
			/////////////
            // PRESETS //
            /////////////
            
            if($year == '')
            {
                $now = now();
                $tnow = mdate("%Y", $now);
                $year = $this->jedoxapi->get_area($year_elements, $tnow);
            }
            if($month == '')
            {
                $month = $this->jedoxapi->get_area($month_elements, "MA");
            }
			
			if($product == '')
			{
				$product = $this->jedoxapi->get_area($product_elements, "FP");
			}
			
			////////////
            // TABLES // 
            ////////////
			
			$version_patAT = $this->jedoxapi->get_area($version_elements, "V001,V002,V003,A/T"); // Plan, Actual, Target A/T
			$version_p = $this->jedoxapi->get_area($version_elements, "V001"); // Plan
			$version_a = $this->jedoxapi->get_area($version_elements, "V002"); //actual
			$version_t = $this->jedoxapi->get_area($version_elements, "V003"); //target
			$version_AT = $this->jedoxapi->get_area($version_elements, "A/T"); //A/T
			$version_pat = $this->jedoxapi->get_area($version_elements, "V001,V002,V003");
			
			$margin_value_qty05 = $this->jedoxapi->get_area($margin_value_elements, "QTY05"); // QTY05
			$margin_value_p001 = $this->jedoxapi->get_area($margin_value_elements, "P001"); // P001
			$margin_value_sls04 = $this->jedoxapi->get_area($margin_value_elements, "SLS04"); // SLS04
			$margin_value_sls03 = $this->jedoxapi->get_area($margin_value_elements, "SLS03"); // SLS03
			$margin_value_sls = $this->jedoxapi->get_area($margin_value_elements, "SLS"); // SLS
			$margin_value_craw = $this->jedoxapi->get_area($margin_value_elements, "CRAW"); // CRAW
			$margin_value_mg01 = $this->jedoxapi->get_area($margin_value_elements, "MG01"); // MG01
			$margin_value_sc03 = $this->jedoxapi->get_area($margin_value_elements, "SC03"); // SC03
			$margin_value_mg02 = $this->jedoxapi->get_area($margin_value_elements, "MG02"); // MG02
			$margin_value_scf = $this->jedoxapi->get_area($margin_value_elements, "SCF"); // SCF
			$margin_value_mg03 = $this->jedoxapi->get_area($margin_value_elements, "MG03"); // MG03
			
			$margin_value_bc = $this->jedoxapi->get_area($margin_value_elements, "BC"); // BC
			$margin_value_wr = $this->jedoxapi->get_area($margin_value_elements, "WR"); // WR
			
			// Areas
			
			$table1_area = $version_patAT.",".$year.",".$month.",".$margin_value_qty05.",".$product;
			$table2_area = $version_patAT.",".$year.",".$month.",".$margin_value_p001.",".$product;
			$table3_area = $version_patAT.",".$year.",".$month.",".$margin_value_sls04.",".$product;
			$table4_area = $version_patAT.",".$year.",".$month.",".$margin_value_sls03.",".$product;
			$table5_area = $version_patAT.",".$year.",".$month.",".$margin_value_sls.",".$product;
			$table6_area = $version_patAT.",".$year.",".$month.",".$margin_value_craw.",".$product;
			$table7_area = $version_patAT.",".$year.",".$month.",".$margin_value_mg01.",".$product;
			$table8_area = $version_patAT.",".$year.",".$month.",".$margin_value_sc03.",".$product;
			$table9_area = $version_patAT.",".$year.",".$month.",".$margin_value_mg02.",".$product;
			$table10_area = $version_patAT.",".$year.",".$month.",".$margin_value_scf.",".$product;
			$table11_area = $version_patAT.",".$year.",".$month.",".$margin_value_mg03.",".$product; 
			
			//added table data
			$table12_area = $version_pat.",".$year.",".$month.",".$margin_value_bc.",".$product; 
			$table13_area = $version_pat.",".$year.",".$month.",".$margin_value_wr.",".$product; 
			
			//$this->jedoxapi->traceme($table4_area);
			// Data 
			
			$table1_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table1_area, "", 1, "", "0");
			$table2_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table2_area, "", 1, "", "0");
			$table3_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table3_area, "", 1, "", "0");
			$table4_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table4_area, "", 1, "", "0");
			$table5_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table5_area, "", 1, "", "0");
			$table6_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table6_area, "", 1, "", "0");
			$table7_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table7_area, "", 1, "", "0");
			$table8_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table8_area, "", 1, "", "0");
			$table9_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table9_area, "", 1, "", "0");
			$table10_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table10_area, "", 1, "", "0");
			$table11_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table11_area, "", 1, "", "0");
			
			$table12_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table12_area, "", 1, "", "0");
			$table13_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$table13_area, "", 1, "", "0");
			
			////////////
			// CHARTS //
			////////////
			
			$version_area_at = $this->jedoxapi->get_area($version_elements, "V002,V003"); // Actual, Target
            $version_elements_at = $this->jedoxapi->dimension_elements_id($version_elements, "V002,V003");
            $version_elements_at_alias = $this->jedoxapi->set_alias($version_elements_at, $cells_version_alias);
			
			$version_elements_a = $this->jedoxapi->dimension_elements_id($version_elements, "V002");
            $version_elements_a_alias = $this->jedoxapi->set_alias($version_elements_a, $cells_version_alias);
			
			$month_all = $this->jedoxapi->dimension_elements_base($month_elements);
            $month_all_alias = $this->jedoxapi->set_alias($month_all, $cells_month_alias);
            $month_all_area = $this->jedoxapi->get_area($month_all);
            
			$product_set = $this->jedoxapi->dimension_elements_base($form_product);
			$product_set_area = $this->jedoxapi->get_area($product_set);
			
			$product_data = $this->jedox->get_dimension_data_by_id($product_elements, $product);
			$product_base = $this->jedoxapi->array_element_filter($product_elements, $product_data['name_element']);
			//$this->jedoxapi->traceme($product_base);
			//$product_base = $this->jedoxapi->dimension_elements_base($product_base);
			$product_base = $this->jedoxapi->set_alias($product_base, $cells_product_alias);
			//$this->jedoxapi->traceme($product_base);
			$product_base_area = $this->jedoxapi->get_area($product_base);
			
			$chart1 = $this->jedox->multichart_xml_categories($month_all_alias, 1);
			
			//chart1
			
			$chart1a_area = $version_a.",".$year.",".$month_all_area.",".$margin_value_sls.",".$product;
			
			$chart1b_area = $version_area_at.",".$year.",".$month_all_area.",".$margin_value_craw.",".$product; 
			$chart1c_area = $version_area_at.",".$year.",".$month_all_area.",".$margin_value_sc03.",".$product;
			$chart1d_area = $version_area_at.",".$year.",".$month_all_area.",".$margin_value_scf.",".$product;
			
			$chart1a_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$chart1a_area, "", 1, "", "0");
			$chart1b_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$chart1b_area, "", 1, "", "0");
			$chart1c_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$chart1c_area, "", 1, "", "0");
			$chart1d_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$chart1d_area, "", 1, "", "0");
			
			$chart1e_data = $this->jedox->add_cell_array($chart1b_data, $chart1c_data, 0, 2);
			$chart1e_data = $this->jedox->add_cell_array($chart1e_data, $chart1d_data, 0, 2);
			
			$chart1 .= $this->jedox->multichart_xml_series($chart1a_data, $month_all, $version_elements_a_alias, 2, 0, "", " Rev");
			$chart1 .= $this->jedox->multichart_xml_series($chart1e_data, $month_all, $version_elements_at_alias, 2, 0, "", " Cost");
			
			//$chart2_area = $version_a.",".$year.",".$month.",".$margin_value_mg03.",".$product_set_area;
			$chart2_area = $version_a.",".$year.",".$month.",".$margin_value_mg03.",".$product_base_area; 
			$chart2_data = $this->jedoxapi->cell_export($server_database['database'],$margin_report_cube_info['cube'],10000,"",$chart2_area, "", 1, "", "0");
			
			//$chart2 = $this->jedox->singlechart_xml($chart2_data, $product_set, 4);
			$chart2 = $this->singlechart_xml($chart2_data, $product_base, 4);
			
			$alldata = array(
				//regular vars here
				"jedox_user_details" => $this->session->userdata('jedox_user_details'),
				"pagename" => $pagename,
				"oneliner" => $oneliner,
				"year" => $year,
				"month" => $month,
				"product" => $product,
				"form_year" => $form_year,
				"form_months" => $form_months,
				"form_product" => $form_product,
				"version_p" => $version_p,
				"version_a" => $version_a,
				"version_t" => $version_t,
				"version_AT" => $version_AT,
				"table1_data" => $table1_data,
				"table2_data" => $table2_data,
				"table3_data" => $table3_data,
				"table4_data" => $table4_data,
				"table5_data" => $table5_data,
				"table6_data" => $table6_data,
				"table7_data" => $table7_data,
				"table8_data" => $table8_data,
				"table9_data" => $table9_data,
				"table10_data" => $table10_data,
				"table11_data" => $table11_data,
				"table12_data" => $table12_data,
				"table13_data" => $table13_data,
				"chart1" => $chart1,
				"chart2" => $chart2
			);
			// Pass data and show view
			$this->load->view("profitability_product_group_brewing_view", $alldata);
		}
	}

	public function singlechart_xml($cells, $series, $series_path, $color = '')
	{
		$series_xml = "";
		$setcolor = "";
		foreach($series as $rows)
		{
			
			foreach($cells as $subrows){
				$cat_identifier = explode(',',$subrows['path']);
				$series_ident = $rows['element'];
				if( $series_ident == $cat_identifier[$series_path] && $subrows['value'] != '' )
				{
					if($color != ''){
						$setcolor = "color='".$color."'";
					}
					
					$series_xml .= "<set label='".$rows['name_element']."' value='".round($subrows['value'])."' ".$setcolor." />";
				}
			}
			
		}
		
		return $series_xml;
	}

}