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
class PessoaList extends TStandardList
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
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('permission');            // defines the database
        parent::setActiveRecord('Pessoa');   // defines the active record
        parent::setDefaultOrder('nome', 'asc');         // defines the default order
        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        
        /*Filtro de listagem de pessoas - se o usuário for admin, lista todos.
         * se o usuário for diferente de admin mostra os usuários de sua competência*/
        
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php
            if(TSession::getValue('tipo') != 'S'){
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));                              
               parent::setCriteria($criteria);   
            }            
            if(TSession::getValue('tipo') == 'S'){
               $criteria = new TCriteria;
               //$criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));               
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                
               parent::setCriteria($criteria);   
            }    
        }    
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Pessoa');
        $this->form->setFormTitle( 'Gestão de Pessoas' );
        
        // create the form fields
        //$id = new TEntry('id');        
        $nome = new TEntry('nome');        
        
        // add the fields
        //$this->form->addFields( [new TLabel('Id')], [$id] );        
        $this->form->addFields( [new TLabel('Nome')], [$nome] );        

        //$id->setSize('30%');        
        $nome->setSize('70%');        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Pessoa_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('PessoaForm', 'onEdit')), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'center', 50);        
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');                 
        $column_telefone = new TDataGridColumn('telefone', 'Telefone', 'left');
        $column_tipo = new TDataGridColumn('tipo','Tipo', 'left');
        $column_status = new TDataGridColumn('status', _t('Active'), 'center');
        $column_id_filial = new TDataGridColumn('filial->nome', 'Filial', 'left');
        
        $column_tipo->enableAutoHide(500);
        $column_tipo->enableAutoHide(500);
        $column_status->enableAutoHide(500);
        $column_id_filial->enableAutoHide(500);

        $column_tipo->enableAutoHide(500);
        $column_status->enableAutoHide(500);

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);        
        $this->datagrid->addColumn($column_nome);                  
        $this->datagrid->addColumn($column_telefone); 
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_id_filial);


        // creates the datagrid column actions
        //$order_id = new TAction(array($this, 'onReload'));
        //$order_id->setParameter('order', 'id');
        //$column_id->setAction($order_id);
        
        $column_status->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        $column_tipo->setTransformer( function($value, $object, $row) {                        
            if($value=='A'){
                $label = 'Atendido';
            }if($value=='D'){
                $label = 'Doador/Patrocinador';
            }if($value=='F'){
                $label = 'Funcionário';
            }if($value=='R'){
                $label = 'Fornecedor';
            }if($value=='V'){
                $label = 'Voluntário';
            }
            $div = new TElement('span');
            //$div->class="label label-{$class}";
            //$div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        $order_nome = new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $column_nome->setAction($order_nome);
        
        $order_filial = new TAction(array($this, 'onReload'));
        $order_filial->setParameter('order', 'id_filial');
        $column_id_filial->setAction($order_filial);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('PessoaForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);     
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        
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
    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('permission');
            $user = Pessoa::find($param['id']);
            if ($user instanceof Pessoa)
            {
                $user->status = $user->status == 'Y' ? 'N' : 'Y';
                $user->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
