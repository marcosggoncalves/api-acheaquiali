<?php

	class Categoria 
	{
		private $conection;

		function __construct($db)
		{
			$this->conection = $db;
		}

		public function getCategorias(){
			$stmt = $this->conection->prepare('select Id, descricao from tab_categorias where ativo = "on" order by descricao');
			$stmt->execute();

			$resultCategorias = [];

			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$resultCategorias[] = $row;
			}			

			return $resultCategorias;
		}

		public function searchCategorias($search = null)
		{

			// Buscar categorias pelo cliente
			$stmtClientes = $this->conection->prepare("
				SELECT tbca.id_categoria as id_categoria FROM tab_clientes tbc
				inner join tab_clientes_categorias tbca on tbc.Id = tbca.id_cliente
				where LOWER(tbc.tags) like  CONCAT('%', LOWER(:search), '%') or 
				LOWER(tbc.descricaodaempresa) like  CONCAT('%', LOWER(:search), '%')
			");

			$stmtClientes->bindParam(":search", $search);
			$stmtClientes->execute();

			$resultCategorias = [];

			while($row = $stmtClientes->fetch(PDO::FETCH_ASSOC)){

				$queryCategorias  = $this->conection->prepare('
					select * from tab_categorias where Id = :categorias and ativo = "on"  order by descricao'
				);

				$queryCategorias->bindParam(":categorias", $row['id_categoria']);
				$queryCategorias->execute();

				while($rowCategorias = $queryCategorias->fetch(PDO::FETCH_ASSOC)){
					$resultCategorias[] = $rowCategorias;
				}	
			}

			if(count($resultCategorias) > 0) {
				return $resultCategorias;
			}

			// -----------------------------------------------------------

			$stmtCategoriasSingle = $this->conection->prepare("
				select * from tab_categorias 
				where LOWER(descricao)
				like CONCAT('%', LOWER(:search), '%') 
				and ativo = 'on'  order by descricao
			");

			$stmtCategoriasSingle->bindParam(":search", $search);
			$stmtCategoriasSingle->execute();
	
			while($rowCategoriasSingle = $stmtCategoriasSingle->fetch(PDO::FETCH_ASSOC)){
				$resultCategorias[] = $rowCategoriasSingle;
			}	

			return $resultCategorias;
		}

		public function getEmpresasCategorias($categoria, $cidade){
			$resultEmpresas = [];

			$query  = $this->conection->prepare("
				select 
				c.*,
				cs.nome,
				concat('http://acheiaquiali.com.br/sistema/arquivos/clientes/', c.diretorio ,'/', imagem) as logo
				from tab_clientes c 
				left join tab_clientes_logotipo l on c.Id = l.id_cliente
				left join  tab_cidades cs on cs.Id = c.end_cidade
				inner join tab_clientes_categorias tc on tc.id_cliente = c.Id 
				where tc.id_categoria =  :categoria and c.ativo = 'on' 
				and cs.Id = {$cidade}
			");
			$query->bindParam(":categoria", $categoria);
			$query->execute();

			while($row = $query->fetch(PDO::FETCH_ASSOC)){
				$resultEmpresas[] = $row;
			}	

			return $resultEmpresas;
		}	

		public function getEmpresasCategoriasSearch($categoria, $search, $cidade){

			$resultEmpresas = [];

			$sql = "select 
				c.*,
				concat('http://acheiaquiali.com.br/sistema/arquivos/clientes/', c.diretorio ,'/', imagem) as logo
				from tab_clientes c 
				left join tab_clientes_logotipo l on c.Id = l.id_cliente
				inner join tab_clientes_categorias tc on tc.id_cliente = c.Id 
				where 
				c.ativo = 'on'
				and tc.id_categoria = :categoria
				and  c.end_cidade = {$cidade}";


			$query  = $this->conection->prepare($sql);

			$query->bindParam(":categoria", $categoria);

			$query->execute();

			while($row = $query->fetch(PDO::FETCH_ASSOC)){
				$resultEmpresas[] = $row;
			}	

			return $resultEmpresas;
		}	

		public function clickCategoria($id = null){
			$sql = "INSERT INTO tab_cliques ( dhcad, quemcriou, id_categoria) VALUES (now(), 'Aplicativo Mobile', :id)";
			$stmt = $this->conection->prepare($sql);
			$stmt->bindParam(":id", $id);

			if($stmt->execute()){
				return true;
			}
			return false;
		}
	}
 ?>
