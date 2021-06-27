<?php
/**
 * arquivo derivado dos arquivos do Adianti Framework
 * @version    1.0
 * @package    sisorg
 * @subpackage control
 * @author     Jacques Geraldo Rocha
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PlanoContaList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
     /**
     * Page constructor
     */
    
    //http://www.portaldecontabilidade.com.br/guia/planodecontas.htm
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('permission');            // defines the database
        parent::setActiveRecord('PlanoConta');   // defines the active record
        parent::setDefaultOrder('codigo_conta', 'asc');         // defines the default order
        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('descricao', 'like', 'descricao'); // filterField, operator, formField        
        parent::addFilterField('id_matriz', '=', 'id_matriz'); // filterField, operator, formField        
         
         
        if (TSession::getValue('login') != 'admin') {          
               $criteria = new TCriteria;
               //$criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));               
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                               
               parent::setCriteria($criteria);  
           TTransaction::open("enfermeirovirtual");
                $criteria2 = new TCriteria;  
                $criteria2->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                               
                $repository = new TRepository('PlanoConta'); 
                $count = $repository->count($criteria2); 
                if($count == 0){
                    $newTab = new PlanoConta();
                        $newTab->id_conta_superior = 1;
                        $newTab->codigo_conta = 1;
                        $newTab->descricao = 'ATIVO';
                        $newTab->contador = 0;
                        $newTab->id_matriz = TSession::getValue('id_matriz');        
                        $newTab->id_filial = TSession::getValue('id_filial');                            
                    $newTab2 = new PlanoConta();
                        $newTab2->id_conta_superior = 2;
                        $newTab2->codigo_conta = 2;
                        $newTab2->descricao = 'PASSIVO';
                        $newTab2->contador = 0;
                        $newTab2->id_matriz = TSession::getValue('id_matriz');        
                        $newTab2->id_filial = TSession::getValue('id_filial');    
                    $newTab3 = new PlanoConta();
                        $newTab3->id_conta_superior = 3;
                        $newTab3->codigo_conta = 3;
                        $newTab3->descricao = 'RECEITA';
                        $newTab3->contador = 0;
                        $newTab3->id_matriz = TSession::getValue('id_matriz');        
                        $newTab3->id_filial = TSession::getValue('id_filial'); 
                    $newTab4 = new PlanoConta();
                        $newTab4->id_conta_superior = 4;
                        $newTab4->codigo_conta = 4;
                        $newTab4->descricao = 'DESPESA';
                        $newTab4->contador = 0;
                        $newTab4->id_matriz = TSession::getValue('id_matriz');        
                        $newTab4->id_filial = TSession::getValue('id_filial'); 
                    $newTab->store();   
                    $newTab2->store(); 
                    $newTab3->store(); 
                    $newTab4->store(); 
                }
            TTransaction::close();
        }
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_PlanoConta');
        $this->form->setFormTitle( 'Plano de Contas' );
        
        // create the form fields
        //$id = new TEntry('id');
        $descricao = new TEntry('descricao');   
        if (TSession::getValue('login') != 'admin') { 
            $criteria3 = new TCriteria;                    
            $criteria3->add(new TFilter('id', '=', TSession::getValue('id_matriz')));      
            $id_matriz = new TDBUniqueSearch('id_matriz', 'enfermeirovirtual', 'Matriz', 'id', 'nome', 'nome', $criteria3);
        }else{
            $id_matriz = new TDBUniqueSearch('id_matriz', 'enfermeirovirtual', 'Matriz', 'id', 'nome', 'nome');
        } 
        
        //$periodo   = new TCombo('periodo');
        //$escolha_perido = array();
        //$escolha_perido['M'] = 'Manhã';
        //$escolha_perido['T'] = 'Tarde';
        //$escolha_perido['N'] = 'Noite';
        //$periodo->addItems($escolha_perido);
        
        // add the fields        
        $this->form->addFields( [new TLabel('Descricao')], [$descricao] );  
        $this->form->addFields( [new TLabel('Matriz')], [$id_matriz] );
        //$this->form->addFields( [new TLabel('Período')], [$periodo] ); 
        
        $id_matriz->setMinLength(1);
        
        $descricao->setSize('100%');        
        $id_matriz->setSize('100%');        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('PlanoConta_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('PlanoContaForm', 'onEdit')), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);      
        
        
        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_codigo_conta = new TDataGridColumn('codigo_conta', 'Código Conta', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');        
        $column_conta_superior = new TDataGridColumn('id_conta_superior', 'Conta Superior', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        
        $column_conta_superior->enableAutoHide(500);
        $column_valor->enableAutoHide(500);

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_codigo_conta);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_conta_superior);
        $this->datagrid->addColumn($column_valor);

//        $column_periodo->setTransformer( function($value, $object, $row) {                        
//            if($value=='M'){
//                $label = 'Manhã';
//            }if($value=='T'){
//                $label = 'Tarde';
//            }if($value=='N'){
//                $label = 'Noite';
//            }
//            $div = new TElement('span');
//            //$div->class="label label-{$class}";
//            //$div->style="text-shadow:none; font-size:12px; font-weight:lighter";
//            $div->add($label);
//            return $div;
//        });

        // creates the datagrid column actions
        /**$order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);**/
        
        $order_codigo_conta = new TAction(array($this, 'onReload'));
        $order_codigo_conta->setParameter('order', 'codigo_conta');
        $column_codigo_conta->setAction($order_codigo_conta);
        
        $order_valor = new TAction(array($this, 'onReload'));
        $order_valor->setParameter('order', 'valor');
        $column_valor->setAction($order_valor);
        
        $column_valor->setTransformer(array($this, 'formatValue'));
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('PlanoContaForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        /**$action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);**/     
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }    
    public function formatValue($value, $object)
    {
        if (!$value) {
                $value = 0;
            }
        return "R$ " . number_format($value, 2, ",", ".");
    }
}
