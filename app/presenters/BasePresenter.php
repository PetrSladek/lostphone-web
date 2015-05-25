<?php
/**
 * Hlavní presenter, který zajištujě vše co je potřeba na všech stránkách aplikace
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Presenters;

use App\Model\Image;
use App\Model\User;
use App\Services\ImageService;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls;


abstract class BasePresenter extends Presenter
{

    /**
     * Prihlaseny uzivatel
     * @var User
     */
    public $me;

    /**
     * Doctrine Entity manažer
     * @var EntityManager
     * @inject Připojí se sám z DI kontejneru
     */
    public $em;

    /**
     * Služba pro práci s obrázky
     * @var ImageService
     * @inject Připojí se sám z DI kontejneru
     */
    public $imageService;

    /**
     * Parametry z konfigurace v config.local.neon a config.neon
     * @var ArrayHash
     */
    public $config;

    /**
     * Metoda, která se spouští na začátku životního cyklu HTTP requestu
     */
    protected function startup()
    {
        parent::startup();

        $config = $this->context->parameters;
        $this->config = ArrayHash::from($config);
        $this->template->config = $this->config;

        $this->me = $this->user->isLoggedIn() ? $this->em->getRepository(User::getClassName())->find( $this->user->getId() ) : null;
        $this->template->me = $this->me;

    }

    /**
     * Vytváření šablony
     * @return \Nette\Application\UI\ITemplate
     */
    protected function createTemplate()
    {
        $template =  parent::createTemplate();

        // Připojíme helper na vypsání URL adresy miniatury
        $template->registerHelper('thumb', $this->thumbLink);

        return $template;
    }


    /**
     * Helper, který vrátí URL adresu zmenšeniny obrázku
     * @param Image $entity Entita obrázku z databáze
     * @param string $type Typ zmenšeniny z konfigurace
     * @return string URL adresa zacachované zmenšeniny obrázku
     */
    public function thumbLink(Image $entity, $type = 'thumb') {
        try {
            return $this->imageService->getThumbnailUrl($entity->getId(), $entity->getFilename(), $type);
        } catch (\Exception $e) {
            return "#{$e->getMessage()}";
        }
    }


    /**
     * Metoda pro upravení renderedu formuláře na styl Bootstrap3
     * @param $form Instance formuláře
     * @param bool $ajax Má se formulář odesálat ajaxem?
     */
    public function prepareRenderer(&$form, $ajax = false) {
        // setup form rendering
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal' . ($ajax ? ' ajax' : null));

        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;

            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');

            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }

    }





}
