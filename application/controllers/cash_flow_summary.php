<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cash_Flow_Summary extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library("jedox");
        $this->load->library("jedoxapi");
		$this->load->library("irrhelper");
    }
    
    public function index ()
    {
        $pagename = "proEO Cash Flow Summary";
        $oneliner = "One-liner here for Cash Flow Summary";
        $user_details = $this->session->userdata('jedox_user_details');
        if($this->session->userdata('jedox_sid') == '' || $this->session->userdata('jedox_sid') == NULL)
        {
            $this->session->set_userdata('jedox_referer', current_url());
            redirect("/login/page");
        }
        else if($this->jedoxapi->page_permission($user_details['group_names'], "efficiency_costs") == FALSE)
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
            $cube_names = "Benefit,#_Project,#_Year,#_Value_Element,#_Customer,#_Benefit_Element,#_Cost_of_Goods";
            
            // Initialize post data //
            //$year = $this->input->post("year");
            $customer = $this->input->post("customer");
            $project = $this->input->post("project");
            
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
            $benefit_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "Benefit");
            
            $benefit_cube_info = $this->jedoxapi->get_cube_data($database_cubes, "Benefit"); 
            
            ////////////////////////////
            // Get Dimension elements //
            ////////////////////////////
            
            // Project //
            // Get dimension of project
            $project_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[0]);
            // Get cube data of project alias
            $project_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Project");
            $project_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Project");
            // Export cells of project alias
            $project_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $project_dimension_id[0]);
			$project_alias_name_id = $this->jedoxapi->get_area($project_alias_elements, "Name");
            $cells_project_alias = $this->jedoxapi->cell_export($server_database['database'],$project_alias_info['cube'],10000,"",$project_alias_name_id.",*");
            
            // YEAR //
            // Get dimension of year
            //$year_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[1]);
			$year_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[1]);
            $year_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Year");
            $year_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Year");
			$year_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $year_dimension_id[0]);
			$year_alias_name_id = $this->jedoxapi->get_area($year_alias_elements, "Name");
            $cells_year_alias = $this->jedoxapi->cell_export($server_database['database'],$year_alias_info['cube'],10000,"",$year_alias_name_id.",*");
            
            // Value_Element //
            // Get dimension of Value_Element
            $value_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[2]);
            // Get cube data of Value_Element alias
            $value_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Value_Element");
            $value_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Value_Element");
            // Export cells of Value_Element alias
            $value_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $value_dimension_id[0]);
			$value_alias_name_id = $this->jedoxapi->get_area($month_alias_elements, "Name");
            $cells_value_alias = $this->jedoxapi->cell_export($server_database['database'],$value_alias_info['cube'],10000,"", $value_alias_name_id.",*");
            
            // Customer //
            // Get dimension of Customer
            $customer_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[3]);
            // Get cube data of Customer alias
            $customer_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Customer");
            $customer_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Customer");
            // Export cells of Customer alias
            $customer_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $customer_dimension_id[0]);
			$customer_alias_name_id = $this->jedoxapi->get_area($customer_alias_elements, "Name");
            $cells_customer_alias = $this->jedoxapi->cell_export($server_database['database'],$customer_alias_info['cube'],10000,"", $customer_alias_name_id.",*"); 
            
            // Benefit_Element //
            // Get dimension of Benefit_Element
            $benefit_element_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[4]);
            // Get cube data of Benefit_Element alias
            $benefit_element_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Benefit_Element");
            $benefit_element_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Benefit_Element");
            // Export cells of Benefit_Element alias
            $benefit_element_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_element_dimension_id[0]);
			$benefit_element_alias_name_id = $this->jedoxapi->get_area($benefit_element_alias_elements, "Name");
            $cells_benefit_element_alias = $this->jedoxapi->cell_export($server_database['database'],$benefit_element_alias_info['cube'],10000,"", $benefit_element_alias_name_id.",*"); 
            
            // Cost_of_Goods //
            // Get dimension of Cost_of_Goods
            $cost_of_goods_elements = $this->jedoxapi->dimension_elements($server_database['database'], $benefit_dimension_id[5]);
            // Get cube data of Cost_of_Goods alias
            $cost_of_goods_dimension_id = $this->jedoxapi->get_dimension_id($database_cubes, "#_Cost_of_Goods");
            $cost_of_goods_alias_info = $this->jedoxapi->get_cube_data($database_cubes, "#_Cost_of_Goods");
            // Export cells of cost_of_goods alias
            $cost_of_goods_alias_elements = $this->jedoxapi->dimension_elements($server_database['database'], $cost_of_goods_dimension_id[0]);
			$cost_of_goods_alias_name_id = $this->jedoxapi->get_area($cost_of_goods_alias_elements, "Name");
            $cells_cost_of_goods_alias = $this->jedoxapi->cell_export($server_database['database'],$cost_of_goods_alias_info['cube'],10000,"", $cost_of_goods_alias_name_id.",*"); 
            
            
            // FORM DATA //
            //$form_year = $this->jedoxapi->array_element_filter($year_elements, "YA"); // all years
			//$form_year = $this->jedoxapi->set_alias($form_year, $cells_year_alias);
            
            $form_customer = $this->jedoxapi->array_element_filter($customer_elements, "CA"); // All customer
            $form_customer = $this->jedoxapi->set_alias($form_customer, $cells_customer_alias); // Set aliases
            
            $form_project = $this->jedoxapi->array_element_filter($project_elements, "PA"); 
            $form_project = $this->jedoxapi->set_alias($form_project, $cells_project_alias); // Set aliases
            
            //$this->jedoxapi->traceme($form_customer, "customer");
			//$this->jedoxapi->traceme($form_project, "project");
			
			
            
            /////////////
            // PRESETS //
            /////////////
            
            if($customer == '')
            {
                $customer = $this->jedoxapi->get_area($customer_elements, "CA");
            }
            if($project == '')
            {
                $project = $this->jedoxapi->get_area($project_elements, "PA");
            }
            
            ////////////////
            // table data //
            ////////////////
            
            $benefit_element_be_000 = $this->jedoxapi->get_area($benefit_element_elements, "BE_000");
			$cost_of_goods_elements_cg_000 = $this->jedoxapi->get_area($cost_of_goods_elements, "CG_000");
			
			$year_elements_set = $this->jedoxapi->array_element_filter($year_elements, "YA");
			$year_elements_set = $this->jedoxapi->set_alias($year_elements_set, $cells_year_alias);
			array_shift($year_elements_set);
			$year_elements_set_area = $this->jedoxapi->get_area($year_elements_set);
			
			$value_elements_set = $this->jedoxapi->dimension_sort_by_name($value_elements, "VE_400_100,VE_400_101,VE_400_102,VE_400_103,VE_400_104,VE_400_105,VE_400_200,VE_400_201,VE_400_202,VE_400_300,VE_400_400,VE_400_500,VE_400_600");
			$value_elements_set_area = $this->jedoxapi->get_area($value_elements_set);
			$value_elements_set_alias = $this->jedoxapi->set_alias($value_elements_set, $cells_value_alias);
			
			$value_element_ve_400_100 = $this->jedoxapi->get_area($value_elements, "VE_400_100");
			$value_element_ve_400_101 = $this->jedoxapi->get_area($value_elements, "VE_400_101");
			$value_element_ve_400_102 = $this->jedoxapi->get_area($value_elements, "VE_400_102");
			$value_element_ve_400_103 = $this->jedoxapi->get_area($value_elements, "VE_400_103");
			$value_element_ve_400_104 = $this->jedoxapi->get_area($value_elements, "VE_400_104");
			$value_element_ve_400_105 = $this->jedoxapi->get_area($value_elements, "VE_400_105");
			$value_element_ve_400_200 = $this->jedoxapi->get_area($value_elements, "VE_400_200");
			$value_element_ve_400_201 = $this->jedoxapi->get_area($value_elements, "VE_400_201");
			$value_element_ve_400_202 = $this->jedoxapi->get_area($value_elements, "VE_400_202");
			$value_element_ve_400_300 = $this->jedoxapi->get_area($value_elements, "VE_400_300");
			$value_element_ve_400_400 = $this->jedoxapi->get_area($value_elements, "VE_400_400");
			$value_element_ve_400_500 = $this->jedoxapi->get_area($value_elements, "VE_400_500");
			$value_element_ve_400_600 = $this->jedoxapi->get_area($value_elements, "VE_400_600");
			
			$value_element_ve_1000_100 = $this->jedoxapi->get_area($value_elements, "VE_1000_100");
			//additional post data
			
			$Update = $this->input->post("Update");
			
			if($Update == "Update")
			{
				echo "the data has been updated!";
				foreach($year_elements_set as $row)
				{
					foreach ($value_elements_set as $row1) {
						$var_data = $this->input->post('var_'.$row['element'].'_'.$row1['element']);
						//save only those with values
						if($var_data != '')
						{
							$update_path = $project.",".$row['element'].",".$row1['element'].",".$customer.",".$benefit_element_be_000.",".$cost_of_goods_elements_cg_000;
							
							$update_data = $this->jedoxapi->cell_replace($server_database['database'], $benefit_cube_info['cube'], $update_path, $var_data);
						}
						
					}
					
				}
			}
			
            $tc_area = $project.",".$year_elements_set_area.",".$value_elements_set_area.",".$customer.",".$benefit_element_be_000.",".$cost_of_goods_elements_cg_000;
            
            $tc_cells = $this->jedoxapi->cell_export($server_database['database'], $benefit_cube_info['cube'], 10000, "", $tc_area, "", "1", "", "0");
            
			//$this->jedoxapi->traceme($tc_cells);
			
			$npvrate_area = $project.",".$year_elements_set[0]['element'].",".$value_element_ve_1000_100.",".$customer.",".$benefit_element_be_000.",".$cost_of_goods_elements_cg_000;
			$npvrate = $this->jedoxapi->cell_export($server_database['database'], $benefit_cube_info['cube'], 10000, "", $npvrate_area, "", "1", "", "0");
			
            ///////////
            // CHART //
            ///////////
            
            // Pass all data to send to view file
            $alldata = array(
                //regular vars here
                "form_customer" => $form_customer,
                "customer" => $customer,
                "form_project" => $form_project,
                "project" => $project,
                "jedox_user_details" => $user_details,
                "pagename" => $pagename,
                "oneliner" => $oneliner,
                "tc_cells" => $tc_cells,
                
                "value_elements_set_alias" => $value_elements_set_alias,
                "year_elements_set" => $year_elements_set,
                
				"value_element_ve_400_100" => $value_element_ve_400_100,
				"value_element_ve_400_101" => $value_element_ve_400_101,
				"value_element_ve_400_102" => $value_element_ve_400_102,
				"value_element_ve_400_103" => $value_element_ve_400_103,
				"value_element_ve_400_104" => $value_element_ve_400_104,
				"value_element_ve_400_105" => $value_element_ve_400_105,
				"value_element_ve_400_200" => $value_element_ve_400_200,
				"value_element_ve_400_201" => $value_element_ve_400_201,
				"value_element_ve_400_202" => $value_element_ve_400_202,
				"value_element_ve_400_300" => $value_element_ve_400_300,
				"value_element_ve_400_400" => $value_element_ve_400_400,
				"value_element_ve_400_500" => $value_element_ve_400_500,
				"value_element_ve_400_600" => $value_element_ve_400_600,
				
				"npvrate" => $npvrate
				
                //trace vars here
                
            );
            // Pass data and show view
            $this->load->view("cash_flow_summary_view", $alldata);
        }
    }
    
}