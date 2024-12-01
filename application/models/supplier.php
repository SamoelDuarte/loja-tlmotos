<?php
class Supplier extends Person
{
	/*
	Determines if a given person_id is a customer
	*/
	function exists($person_id)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id', $person_id);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}

	/*
	Returns all the suppliers
	*/
	function get_all($limit = 10000, $offset = 0)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->order_by("last_name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('suppliers');
		$this->db->where('deleted', 0);
		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular supplier
	*/
	function get_info($supplier_id)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id', $supplier_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $supplier_id is NOT an supplier
			$person_obj = parent::get_info(-1);

			//Get all the fields from supplier table
			$fields = $this->db->list_fields('suppliers');

			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field) {
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Gets information about multiple suppliers
	*/
	function get_multiple_info($suppliers_ids)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where_in('suppliers.person_id', $suppliers_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();
	}

	/*
     * Inserts or updates a supplier
     */
	public function save($person_data, $supplier_id = false, $supplier_data = null)
	{
		$success = false;

		// Inicia a transação
		$this->db->trans_start();

		// Salva os dados da pessoa usando o método da classe pai
		if (parent::save($person_data, $supplier_id)) {
			// Verifica se o fornecedor não existe (inserção) ou se já existe (atualização)
			if (!$supplier_id || !$this->exists($supplier_id)) {
				// Se não existe, insere um novo fornecedor
				$supplier_data['person_id'] = $person_data['person_id']; // Atribui person_id aos dados do fornecedor
				$success = $this->db->insert('suppliers', $supplier_data);
				$supplier_id = $this->db->insert_id(); // Captura o ID do novo registro
			} else {
				// Se já existe, atualiza o registro existente
				$this->db->where('person_id', $supplier_id);
				$success = $this->db->update('suppliers', $supplier_data);
			}
		}

		// Completa a transação
		$this->db->trans_complete();

		// Retorna o sucesso da operação
		return $success;
	}

	/*
	Deletes one supplier
	*/
	function delete($supplier_id)
	{
		$this->db->where('person_id', $supplier_id);
		return $this->db->update('suppliers', array('deleted' => 1));
	}

	/*
	Deletes a list of suppliers
	*/
	function delete_list($supplier_ids)
	{
		$this->db->where_in('person_id', $supplier_ids);
		return $this->db->update('suppliers', array('deleted' => 1));
	}

	/*
	Get search suggestions to find suppliers
	*/
	function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("company_name", $search);
		$this->db->order_by("company_name", "asc");
		$by_company_name = $this->db->get();
		foreach ($by_company_name->result() as $row) {
			$suggestions[] = $row->company_name;
		}


		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");
		$by_name = $this->db->get();
		foreach ($by_name->result() as $row) {
			$suggestions[] = $row->first_name . ' ' . $row->last_name;
		}

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("email", $search);
		$this->db->order_by("email", "asc");
		$by_email = $this->db->get();
		foreach ($by_email->result() as $row) {
			$suggestions[] = $row->email;
		}

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("phone_number", $search);
		$this->db->order_by("phone_number", "asc");
		$by_phone = $this->db->get();
		foreach ($by_phone->result() as $row) {
			$suggestions[] = $row->phone_number;
		}

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("account_number", $search);
		$this->db->order_by("account_number", "asc");
		$by_account_number = $this->db->get();
		foreach ($by_account_number->result() as $row) {
			$suggestions[] = $row->account_number;
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		return $suggestions;
	}

	/*
	Get search suggestions to find suppliers
	*/
	function get_suppliers_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("company_name", $search);
		$this->db->order_by("company_name", "asc");
		$by_company_name = $this->db->get();
		foreach ($by_company_name->result() as $row) {
			$suggestions[] = $row->person_id . '|' . $row->company_name;
		}


		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");
		$by_name = $this->db->get();
		foreach ($by_name->result() as $row) {
			$suggestions[] = $row->person_id . '|' . $row->first_name . ' ' . $row->last_name;
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		return $suggestions;
	}
	/*
	Perform a search on suppliers
	*/
	function search($search)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		company_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");

		return $this->db->get();
	}
}
