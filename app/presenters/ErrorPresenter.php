<?php
/**
 * Presenter, který se stará o výpis chybových hlášení (HTTP kody 4xx a 5xx)
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */


namespace App\Presenters;

use Nette,
	App\Model,
	Tracy\Debugger;


class ErrorPresenter extends BasePresenter
{

	/**
	 * @param  Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($exception instanceof Nette\Application\BadRequestException) {
			$code = $exception->getCode();
			// nacte temeplate 403.latte nebo 404.latte nebo ... 4xx.latte
			$this->setView(in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx');
			// Zapíše do access Logu
			Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

		} else {
			$this->setView('500'); // načte template 500.latte
			Debugger::log($exception, Debugger::EXCEPTION); // a zaloguje vyjímku
		}

		if ($this->isAjax()) { // Pokud se jedná o HTTP požadavek z AJAXu, tak vrátí payload s chybou
			$this->payload->error = TRUE;
			$this->terminate();
		}
	}

}
