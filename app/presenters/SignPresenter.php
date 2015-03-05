<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    /** @var \Kdyby\Google\Google @inject */
    public $google;


    /** @return \Kdyby\Google\Dialog\LoginDialog */
    protected function createComponentGoogleLogin()
    {
        $dialog = new \Kdyby\Google\Dialog\LoginDialog($this->google);
        $dialog->onResponse[] = function (\Kdyby\Google\Dialog\LoginDialog $dialog) {
            $google = $dialog->getGoogle();

            if (!$google->getUser()) {
                $this->flashMessage("Přihlášení se nezdařilo!", 'danger');
                return;
            }

            /**
             * If we get here, it means that the user was recognized
             * and we can call the Google API
             */

            try {
                $me = $google->getProfile();

//                dump($me); die;

//                if (!$existing = $this->usersModel->findByGoogleId($google->getUser())) {
//                    /**
//                     * Variable $me contains all the public information about the user
//                     * including Google id, name and email, if he allowed you to see it.
//                     */
//                    $existing = $this->usersModel->registerFromGoogle($google->getUser(), $me);
//                }
//
//                /**
//                 * You should save the access token to database for later usage.
//                 *
//                 * You will need it when you'll want to call Google API,
//                 * when the user is not logged in to your website,
//                 * with the access token in his session.
//                 */
//                $this->usersModel->updateGoogleAccessToken($google->getUser(), $google->getAccessToken());

                /**
                 * Nette\Security\User accepts not only textual credentials,
                 * but even an identity instance!
                 */
//                $this->user->login(new \Nette\Security\Identity($existing->id, $existing->roles, $existing));
                    $this->user->login(new \Nette\Security\Identity("[".$me->id."]", [], $me));
                /**
                 * You can celebrate now! The user is authenticated :)
                 */

            } catch (\Exception $e) {
                /**
                 * You might wanna know what happened, so let's log the exception.
                 *
                 * Rendering entire bluescreen is kind of slow task,
                 * so might wanna log only $e->getMessage(), it's up to you
                 */
                \Tracy\Debugger::log($e, 'google');
                $this->flashMessage("Přihlášení se nezdařilo.", 'danger');
            }

            $this->redirect('Homepage:');
        };

        return $dialog;
    }



//	/**
//	 * Sign-in form factory.
//	 * @return Nette\Application\UI\Form
//	 */
//	protected function createComponentSignInForm()
//	{
//		$form = new Nette\Application\UI\Form;
//		$form->addText('username', 'Username:')
//			->setRequired('Please enter your username.');
//
//		$form->addPassword('password', 'Password:')
//			->setRequired('Please enter your password.');
//
//		$form->addCheckbox('remember', 'Keep me signed in');
//
//		$form->addSubmit('send', 'Sign in');
//
//		// call method signInFormSucceeded() on success
//		$form->onSuccess[] = $this->signInFormSucceeded;
//		return $form;
//	}
//
//
//	public function signInFormSucceeded($form, $values)
//	{
//		if ($values->remember) {
//			$this->getUser()->setExpiration('14 days', FALSE);
//		} else {
//			$this->getUser()->setExpiration('20 minutes', TRUE);
//		}
//
//		try {
//			$this->getUser()->login($values->username, $values->password);
//			$this->redirect('Homepage:');
//
//		} catch (Nette\Security\AuthenticationException $e) {
//			$form->addError($e->getMessage());
//		}
//	}


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
