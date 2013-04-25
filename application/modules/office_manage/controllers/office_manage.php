<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Integrated Human Resource Management Information System
 *
 * An Open Source Application Software use by Government agencies for  
 * management of employees Attendance, Leave Administration, Payroll, 
 * Personnel Training, Service Records, Performance, Recruitment,
 * Personnel Schedule(Plantilla) and more...
 *
 * @package		iHRMIS
 * @author		Manny Isles
 * @copyright	Copyright (c) 2008 - 2013, Charliesoft
 * @license		http://charliesoft.net/ihrmis/license
 * @link		http://charliesoft.net
 * @github	    http://github.com/mannysoft/ihrmis
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * iHRMIS Conversion Table Class
 *
 * This class use for converting number of minutes late
 * to the corresponding equivalent to leave credits.
 *
 * @package		iHRMIS
 * @subpackage	Models
 * @category	Models
 * @author		Manny Isles
 * @link		http://charliesoft.net
 * @github	    http://github.com/mannysoft/ihrmis/hrmis/user_guide/models/conversion_table.html
 */
class Office_Manage extends MX_Controller {

	// --------------------------------------------------------------------
	
	function __construct()
    {
        parent::__construct();
		
		$this->load->model('options');
		
		//$this->output->enable_profiler(TRUE);
    }  
	
	// --------------------------------------------------------------------
	
	function add_office()
	{
		$data['page_name'] = '<b>Add Office</b>';
		
		$data['msg'] = '';
		
		if($this->input->post('op'))
		{
			$this->form_validation->set_rules('office_name', 'Office Name', 'required|callback_office_check');
			
			if ($this->form_validation->run($this) == TRUE)
			{
				//Add the office
				$data = array(
								'office_code' 		=> $this->input->post('office_code'),
								'office_name' 		=> $this->input->post('office_name'),
								'office_address' 	=> $this->input->post('office_address'),
								'salary_grade_type' => $this->input->post('salary_grade_type'),
								'office_head' 		=> $this->input->post('office_head'),
								'employee_id' 		=> $this->input->post('employee_id'),
								'position'	  		=> $this->input->post('position'),
								'office_location' 	=> $this->input->post('office_location'),
								'disbursing_officer'=> $this->input->post('disbursing_officer'),
							);
							
				$this->Office->add_office($data);
				
				$this->session->set_flashdata('msg', 'Office added!');
				
				redirect(base_url().'office_manage/view_offices', 'refresh');
			}
		}
				
		$data['main_content'] = 'add_office';
		
		$this->load->view('includes/template', $data);
	}
	
	// --------------------------------------------------------------------
	
	function edit_office($office_id = '')
	{
		$data['page_name'] = '<b>Edit Office</b>';
		
		$data['msg'] = '';
		
		$data['office'] = $this->Office->get_office_info($office_id);
		
		if($this->input->post('op'))
		{
			$this->form_validation->set_rules('office_name', 'Office Name', 'required');
			
			if ($this->form_validation->run($this) == TRUE)
			{
				$data = array(
								'office_code' 		=> $this->input->post('office_code'),
								'office_name' 		=> $this->input->post('office_name'),
								'office_address' 	=> $this->input->post('office_address'),
								'salary_grade_type' => $this->input->post('salary_grade_type'),
								'office_head'		=> $this->input->post('office_head'),
								'employee_id' 		=> $this->input->post('employee_id'),
								'position'	  		=> $this->input->post('position'),
								'office_location' 	=> $this->input->post('office_location'),
								'disbursing_officer'=> $this->input->post('disbursing_officer'),
							);
							
				$this->Office->update_office($data, $office_id);	
				
				$this->session->set_flashdata('msg', 'Office updated!');
				
				redirect(base_url().'office_manage/view_offices', 'refresh');
			}
		}
				
		$data['main_content'] = 'edit_office';
		
		$this->load->view('includes/template', $data);
	}
	
	
	// --------------------------------------------------------------------
	
	function delete_office($office_id = '')
	{
		// Lets check if the office have associated employee
		$e = new Employee_m();
		$e->where('office_id', $office_id);
		$e->get();
		
		// If exists
		if ($e->exists())
		{
			$this->session->set_flashdata('error_msg', 'Unable to delete office. 
														Please delete all employee associated with the office.');
			redirect(base_url().'office_manage/view_offices', 'refresh');
		}
				
		$this->Office->delete_office($office_id);
		
		// Delete the associated division
		$d = new Division();
		$d->where('office_id', $office_id)->get();
		$d->delete_all();
		
		$this->session->set_flashdata('msg', 'Office deleted!');
		
		redirect(base_url().'office_manage/view_offices', 'refresh');
	}
	
	// --------------------------------------------------------------------
	
	function office_check($office_name)
	{
		$is_office_exists = $this->Office->is_office_exists($office_name);
		
		if ($is_office_exists == TRUE)
		{
			$this->form_validation->set_message('office_check', 'The Office exists!');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	// --------------------------------------------------------------------
	
	function view_offices()
	{
		$data['page_name'] = '<b>Office Management</b>';
		
		$data['msg'] = '';
		
		$this->load->library('pagination');
		
		$data['rows'] = $this->Office->get_offices();
		
		$config['base_url'] = base_url().'office_manage/view_offices';
		$config['total_rows'] = $this->Office->num_rows;
	    $config['per_page'] = '15';
	    $this->config->load('pagination', TRUE);
		
		$pagination = $this->config->item('pagination');
		
		// We will merge the config file of pagination
		$config = array_merge($config, $pagination);
		
		$this->pagination->initialize($config);
		
		$data['rows'] = $this->Office->get_offices( $config['per_page'], $this->uri->segment(3));
		
		$this->pagination->initialize($config);
				
		$data['main_content'] = 'view_offices';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function divisions($office_id = '')
	{
		$o = new Office_m();
		$o->get_by_office_id($office_id);
		
		$data['page_name'] = '<b>Divisions of "'.$o->office_name.'"</b>';
		
		$data['msg'] = '';
		
		$this->load->library('pagination');
		
		$divisions = new Division();
		
		$config['base_url'] = base_url().'training_manage/course';
		$config['total_rows'] = $divisions->count();
		$config['per_page'] = '15';
		$config['full_tag_open'] = '<p>';
	    $config['full_tag_close'] = '</p>';
		
		$this->pagination->initialize($config);
		
		// How many related records we want to limit ourselves to
		$limit = $config['per_page'];
		
		// Set the offset for our paging
		$offset = $this->uri->segment(4);
		
		$divisions->where('office_id', $office_id);
		$divisions->order_by('name');
		
		$data['rows'] = $divisions->get($limit, $offset);
		
		$data['office_id'] = $office_id;
		
		$data['page'] = $this->uri->segment(3);
				
		$data['main_content'] = 'divisions';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function division_save( $office_id = '', $id = '' )
	{
		$data['page_name'] = '<b>Save Division</b>';
		
		$data['msg'] = '';
			
		$divisions = new Division();
		
		$data['division'] = $divisions->get_by_id( $id );
		
		$data['office_id'] = $office_id;
		
		if($this->input->post('op'))
		{
			$divisions->name 		= $this->input->post('name');
			$divisions->description	= $this->input->post('description');
			$divisions->office_id 	= $office_id;
			$divisions->order		= $this->input->post('order');
						
			$divisions->save();
			
			$this->session->set_flashdata('msg', 'Division has been saved!');
			
			redirect(base_url().'office_manage/divisions/'.$office_id, 'refresh');
		}
		
		$data['main_content'] = 'division_save';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function division_delete( $id = '', $office_id = '' )
	{
		$division = new Division();
		
		$division->get_by_id( $id );
		
		$division->delete();
		
		$this->session->set_flashdata('msg', 'Division has been deleted!');
		
		redirect(base_url().'office_manage/divisions/'.$office_id, 'refresh');
		
	}
}	

/* End of file office_manage.php */
/* Location: ./system/application/modules/office_manage/controllers/office_manage.php */