<?php

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Carregar o modelo de item
        $this->load->model('Item');
        $this->load->model('Categorie');
        $this->load->library('WooCommerceLibrary');
        // Atribua a instância à propriedade
        $this->WooCommerceLibrary = new WooCommerceLibrary();
    }

    public function sync_categories()
    {
        // Passo 1: Buscar todos os produtos
        $products = $this->Item->get_all(); // Presume que você tenha um método para obter todos os produtos
        // print_r($products->result());
        // exit;
        foreach ($products->result() as $product) {


            // Pega a categoria do produto como texto
            $category_name = $product->category; // Ajuste para o nome correto da coluna

            // Passo 2: Verificar se a categoria existe na tabela categories_products
            $category = $this->Categorie->get_by_name($category_name); // Presume que você tenha um método para buscar categorias pelo nome

            // Passo 3: Se a categoria não existir, insira-a
            if (!$category) {
                $category_name = ucwords(strtolower($category_name)); // Torna a primeira letra de cada palavra maiúscula
                $category_id = $this->Categorie->insert([
                    'category_name' => $category_name
                ]); // Insere a nova categoria e captura o ID
            } else {
                $category_id = $category->category_id; // Categoria já existe, captura o ID existente
            }

            // Passo 4: Atualizar o produto com o category_id
            $this->Item->update_category_id($product->item_id, $category_id); // Atualiza o produto com o novo category_id
        }
    }

    public function sync_with_woocommerce($up = null)
    {
        $this->load->model('categorie');
        $this->load->model('brand');
        $this->load->model('Item'); // Certifique-se de que o modelo 'Item' está carregado
        $this->load->library('WooCommerceLibrary'); // Certifique-se de carregar a biblioteca WooCommerce

        $items = !empty($up) ? $this->Item->get_up_sinc_wc() : $this->Item->get_sinc_wc();


        foreach ($items as $item_data) {
            // Obter o wc_id da categoria
            $category = $this->categorie->get_wc_id($item_data['category_id']);
            $brand = $this->brand->get_wc_id($item_data['brand_id']);

            if ($brand->wc_id == null) {
                $send_brand = [
                    'name' => $brand->brand_name,
                    'slug' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $brand->brand_name))
                ];
    
                try {
                    $result = $this->WooCommerceLibrary->create_product_brand($send_brand);
                    $this->brand->update($brand->brand_id, ['wc_id' => $result->id]);
                    $brand_term_name = $result->name;
                } catch (Exception $e) {
                    log_message('error', 'Erro ao criar marca: ' . $e->getMessage());
                    $brand_term_name = null;
                }
            } else {
                $brand_term_name = $brand->brand_name;
            }

            if ($category->wc_id == null) {
                // Dados da categoria para envio ao WooCommerce
                $send_category = [
                    'name' => $category->category_name
                ];

                try {
                    // Chama a função da biblioteca WooCommerce para criar a categoria
                    $result = $this->WooCommerceLibrary->create_product_category($send_category);

                    // Atualiza a tabela `categories` com o `wc_id` retornado
                    $update_data = [
                        'wc_id' => $result->id
                    ];
                    $wc_id_category = $result->id;
                    $this->Categorie->update($category->category_id, $update_data);

                    echo json_encode(array('success' => true, 'message' => 'Categoria enviada para o WooCommerce com sucesso.'));
                } catch (Exception $e) {
                    log_message('error', 'Erro ao enviar categoria para WooCommerce: ' . $e->getMessage());
                    echo json_encode(array('success' => false, 'message' => 'Erro ao sincronizar com o WooCommerce.' . $e->getMessage()));
                }
            } else {
                $wc_id_category = $category->wc_id;
            }


            $woo_data = [
                'name' => $item_data['name'],
                'regular_price' => !empty($item_data['sale_price']) ? (string)$item_data['sale_price'] : null, // Preço promocional
                'description' => $item_data['description'],
                'sku' => $item_data['item_number'],
                'stock_quantity' => (int)$item_data['quantity'],
                'manage_stock' => true,
                'status' => 'publish', // Publicar diretamente
                // 'featured' => $item_data['featured'] == 1,
                // 'on_sale' => $item_data['on_sale'] == 1,
            ];

            // Adiciona a categoria se existir um wc_id válido
            if ($wc_id_category) {
                $woo_data['categories'] = [['id' => $wc_id_category]];
            }
            if (!empty($brand_term_name)) {
                // print_r($brand_term_name);
                // exit;
                $woo_data['attributes'][] = [
                    'id' => 1, // ID do atributo "Marca"
                    'options' => [$brand_term_name],
                    'visible' => true,
                    'variation' => false
                ];
            }

            // Adiciona imagens se existirem
            $images = $this->get_item_images($item_data['item_id']);
            if (!empty($images)) {
                $woo_data['images'] = $images;
            }

            // Adiciona peso e dimensões se existirem
            if (!empty($item_data['peso'])) {
                $woo_data['weight'] = (string)$item_data['peso'];  // Envia o peso como string
            }

            if (!empty($item_data['altura']) && !empty($item_data['largura']) && !empty($item_data['comprimento'])) {
                $woo_data['dimensions'] = [
                    'length' => (string)$item_data['comprimento'],  // Comprimento como string
                    'width' => (string)$item_data['largura'],      // Largura como string
                    'height' => (string)$item_data['altura'],      // Altura como string
                ];
            }

            // Verifica se o produto já foi sincronizado pelo `id_wc`
            $this->db->select('id_wc');
            $this->db->from('items');
            $this->db->where('item_id', $item_data['item_id']);
            $result = $this->db->get()->row();

            try {
                if (!empty($result) && !empty($result->id_wc)) {
                    // Atualiza produto existente no WooCommerce
                    $product_id = $result->id_wc;
                    $this->WooCommerceLibrary->update_product($product_id, $woo_data);
                    $this->db->where('id_wc', $result->id_wc);
                    $this->db->update('items', ['up_wc' => 0]);
                    log_message('info', 'Produto atualizado no WooCommerce: ' . $item_data['name']);
                } else {
                    // Cria novo produto no WooCommerce
                    $response = $this->WooCommerceLibrary->create_product($woo_data);

                    if (!empty($response->id)) {
                        // Atualiza `id_wc` no banco de dados local com o ID retornado pelo WooCommerce
                        $this->db->where('item_id', $item_data['item_id']);
                        $this->db->update('items', ['id_wc' => $response->id]);
                        log_message('info', 'Novo produto criado no WooCommerce: ' . $item_data['name']);
                    } else {
                        log_message('error', 'Erro ao criar produto no WooCommerce. Resposta inválida.');
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'Erro ao sincronizar o item com o WooCommerce: ' . $e->getMessage());
                echo 'Erro ao sincronizar o item com o WooCommerce: ' . $e->getMessage();
            }
        }
    }

    // Função para obter as imagens do item
    private function get_item_images($item_id)
    {
        // Seleciona as imagens do banco de dados
        $this->db->select('image_path');
        $this->db->from('item_images');
        $this->db->where('item_id', $item_id);
        $query = $this->db->get();

        $images = [];
        $default_cloud_image = 'https://triunfo.pe.gov.br/pm_tr430/wp-content/uploads/2018/03/sem-foto.jpg'; // URL da imagem padrão na nuvem

        // Organiza as imagens
        foreach ($query->result() as $row) {
            $image_url = base_url($row->image_path); // Converte o caminho relativo para um URL absoluto

            // Substitui URLs de localhost pela imagem padrão
            if (strpos($image_url, 'localhost') !== false) {
                $image_url = $default_cloud_image;
            }

            $images[] = [
                'src' => $image_url, // Adiciona a imagem ajustada (URL local ou padrão)
            ];
        }

        return $images;
    }
}
