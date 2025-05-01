<?php
class Brand extends CI_Model
{
    // Função para buscar uma categoria pelo nome
    public function get_by_name($brand_name)
    {
        // Busca na tabela pos_brand_products pela coluna 'name'
        $this->db->where('brand_name', $brand_name);
        $query = $this->db->get('pos_brand_products');

        // Verifica se a categoria foi encontrada e retorna o resultado
        if ($query->num_rows() > 0) {
            return $query->row(); // Retorna o objeto da categoria
        } else {
            return false; // Se não encontrou, retorna false
        }
    }

    // Função para buscar uma categoria pelo nome
    public function get_info($brand_id)
    {
        // Busca na tabela pos_brand_products pela coluna 'name'
        $this->db->where('brand_id', $brand_id);
        $query = $this->db->get('pos_brand_products');

        // Verifica se a categoria foi encontrada e retorna o resultado
        if ($query->num_rows() > 0) {
            return $query->row(); // Retorna o objeto da categoria
        } else {
            return false; // Se não encontrou, retorna false
        }
    }
    // Busca todas as categorias com paginação e filtros
    public function get_all($limit = null, $offset = null, $search = null)
    {
        if ($search) {
            $this->db->like('brand_name', $search);
        }

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get('brand_products');
        return $query->result_array();
    }

    // Conta o número total de categorias, com ou sem filtros
    public function count_all($search = null)
    {
        if ($search) {
            $this->db->like('brand_name', $search);
        }

        return $this->db->count_all_results('brand_products');
    }



    // Função para excluir uma categoria
    public function delete($brand_id)
    {
        $this->db->where('brand_id', $brand_id);
        return $this->db->delete('pos_brand_products');
    }

    public function get_without_wc_id()
    {
        $this->db->where('wc_id', null);
        $this->db->limit(5);
        $query = $this->db->get('brand_products');


        // Verifica se a categoria foi encontrada e retorna o resultado
        if ($query->num_rows() > 0) {
            return $query->result(); // Retorna o objeto da categoria
        } else {
            return false; // Se não encontrou, retorna false
        }
    }

    public function is_brand_in_use($brand_id)
    {
        // Carregue o modelo de Item
        $this->load->model('Item');

        // Verifica se há itens associados à categoria
        return $this->Item->count_by_brand($brand_id) > 0; // Retorna true se houver itens
    }


    public function get_wc_id($item_id)
    {
        // Se a categoria foi encontrada, busca o wc_id da categoria na tabela de categorias
        $this->db->select('*');  // Seleciona o wc_id
        $this->db->from('pos_brand_products');  // Supondo que a tabela de categorias seja 'pos_brand_products'
        $this->db->where('brand_id', $item_id);
        $brand_query = $this->db->get();
        $brand = $brand_query->row();

        if ($brand) {
            return $brand;  // Retorna o wc_id da categoria
        }


        // Se não encontrar o item ou a categoria, retorna null
        return null;
    }


    // Função para inserir uma nova categoria
    public function insert($brand_data)
    {
        // Insere os dados na tabela pos_brand_products
        $this->db->insert('pos_brand_products', $brand_data);

        // Retorna o ID da nova categoria inserida
        return $this->db->insert_id();
    }
    // Função para atualizar uma categoria
    public function update($brand_id, $data)
    {
        $this->db->where('brand_id', $brand_id);
        return $this->db->update('pos_brand_products', $data);
    }
}
