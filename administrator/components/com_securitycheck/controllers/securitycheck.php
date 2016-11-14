<?php
/**
* Securitycheck Controller para Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load framework base classes
jimport('joomla.application.component.controller');

/**
* Securitycheck Securitycheck Controller
*
*/
class SecuritychecksControllerSecuritycheck extends SecuritycheckController
{
/**
* constructor (registers additional tasks to methods)
* @return void
*/
function __construct()
{
parent::__construct();

}
/**
* Muestra los componentes de la BBDD
*/
function mostrar()
{
$msg = JText::_( 'COM_SECURITYCHECK_SHOW_MSG' );
$this->setRedirect( 'index.php?option=com_securitycheck', $msg );
}

/**
 * Busca cambios entre los componentes almacenados en la BBDD y la BBDD de vulnerabilidades
 */
function buscar()
{
$model = $this->getModel('securitychecks');
if(!$model->buscar()) {
	$msg = JText::_( 'COM_SECURITYCHECK_CHECK_FAILED' );
	JError::errorNotice(500,$msg);
} else {
	$eliminados = JRequest::getVar('comp_eliminados');
	$core_actualizado = JRequest::getVar('core_actualizado');
	$comps_actualizados = JRequest::getVar('componentes_actualizados');
	$comp_ok = JText::_( 'COM_SECURITYCHECK_CHECK_OK' );
	$msg = JText::_( $eliminados ."</li><li>" .$core_actualizado ."</li><li>" .$comps_actualizados ."</li><li>" .$comp_ok );
}
$this->setRedirect( 'index.php?option=com_securitycheck', $msg );
}

/* Ver los logs almacenados por el plugin */
function view_logs()
{
	JRequest::setVar( 'view', 'logs' );
	JRequest::setVar('hidemainmenu', 1);

	parent::display(); 
}

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheck&controller=securitycheck' );
}

/* Redirecciona las peticiones al Panel de Control */
function redireccion_control_panel()
{
	$this->setRedirect( 'index.php?option=com_securitycheck' );
}

/* Filtra los logs seg�n el t�rmino de b�squeda especificado*/
function search()
{
	$model = $this->getModel('logs');
	if(!$model->search()) {
		$msg = JText::_( 'COM_SECURITYCHECK_CHECK_FAILED' );
		JError::errorNotice(500,$msg);
	} else {
		$this->view_logs();
	}
	
}

/**
 * Marcar log(s) como le�dos
 */
function mark_read()
{
	$model = $this->getModel('logs');
	$read = $model->mark_read();
	$this->view_logs();
}

/**
 * Marcar log(s) como no le�dos
 */
function mark_unread()
{
	$model = $this->getModel('logs');
	$read = $model->mark_unread();
	$this->view_logs();
}

/**
 * Borrar log(s) de la base de datos
 */
function delete()
{
	$model = $this->getModel('logs');
	$read = $model->delete();
	$this->view_logs();
}

/**
 * Borrar todos los log(s) de la base de datos
 */
function delete_all()
{
	$model = $this->getModel('logs');
	$read = $model->delete_all();
	$this->view_logs();
}

}