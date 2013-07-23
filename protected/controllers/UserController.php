<?php

class UserController extends Controller
{

    /**
     * @author Nguyen Van Cuong
     */
    public function actionSignIn()
    {
        // return user to requested url if user already signin
        if (Yii::app()->user->getId()) {
            Yii::app()->request->redirect(Yii::app()->user->returnUrl);
        } else {
            $form = new SigninForm;
            if (isset($_POST['SigninForm'])) {
                $form->attributes = $_POST['SigninForm'];
                if ($form->validate() && $form->login()) {
                    $this->afterSignIn();
                    Yii::app()->request->redirect(Yii::app()->user->returnUrl);
                }
            }
        }
        $this->render('signin', array('form' => $form));
    }

    /*
     * @author Nguyen Van Cuong
     */

    private function afterSignIn()
    {
        Yii::app()->session['current_user'] = User::model()->findByPk(Yii::app()->user->id);
    }

    /*
     * @author Cuong, Nguyen Anh Tien
     */

    public function actionSignUp()
    {
        // check for email and secret_key
        if (isset($_GET['email']) && isset($_GET['secret_key'])) {
            $profile = Profile::model()->findByAttributes(
                array('email' => $_GET['email'])
            );
            if ($profile !== null && $profile->secret_key != null && $profile->secret_key == $_GET['secret_key']
            ) {
                $signup_form = new SignUpForm;
                if (isset($_POST['SignUpForm'])) {
                    $signup_form->attributes = $_POST['SignUpForm'];
                    if ($signup_form->validate()) {
                        // create user
                        $user = new User;
                        $user->attributes = $_POST['SignUpForm'];
                        $user->password = $user->generatePasswordHash($signup_form->password);
                        $user->profile_id = $profile->id;
                        $user->save();
                        $profile->updateKey();
                        // signin user
                        $signin_form = new SigninForm;
                        $signin_form->attributes = $_POST['SignUpForm'];
                        $signin_form->login();
                        $this->afterSignIn();
                        $this->redirect(Yii::app()->homeUrl);
                    }
                }
                $this->render('signup', array('form' => $signup_form));
            } else {
                // on invalid case, redirect to home page
                $this->redirect($this->createUrl('user/signin'));
            }
        } else {
            // on invalid case, redirect to home page
            $this->redirect($this->createUrl('user/signin'));
        }
    }

    /**
     * @author Nguyen Anh Tien
     */
    public function actionSignOut()
    {
        Yii::app()->user->logout();
        Yii::app()->request->redirect($this->createUrl('home/index'));
    }

    /*
     * @author Vu Dang Tung
     * Password recover
     */

    public function actionForgetPassword()
    {
        if (isset($_POST['ForgetPasswordForm'])) {

            $profile = ProfileOrUserFinder::findProfile($_POST['ForgetPasswordForm']['arg']);
            if ($profile != null && $profile->user != null) {
                $profile->sendResetPasswordLink();
                Yii::app()->user->setFlash('success', 'We have sent you a link to reset your password. 
                    Please check your email');
                $this->redirect(Yii::app()->user->loginUrl);
            } else {
                Yii::app()->user->setFlash('error', 'Sorry ! No User found !');
            }
        }
        $this->render('forget_password');
    }
   
    /* 
     *@author Nguyen Van Cuong
     * action reset password
     */
     public function actionResetPassword()
    {       
        $profile = Profile::model()->findByAttributes(
            array('email' => $_GET['email'], ));
        if ($profile != null) {   
            $form = new ResetPasswordForm;   
            if (isset($_POST['ResetPasswordForm'])) {             
                $form->attributes = $_POST['ResetPasswordForm'];
                $form->validate();
                if (!$form->hasErrors()) {
                    $user = $profile->user;
                    $user->password = $user->generatePasswordHash($form->password);
                    $user->save();
                    $profile->updateKey(); 
                    Yii::app()->user->setFlash('success', 'Your password has been changed !');
                    $this->redirect(Yii::app()->homeUrl);
                }  
            }
            $this->render('reset_password', array('form' => $form));
        } else {
            Yii::app()->user->setFlash('error', 'Invalid URL !');
            $this->redirect(Yii::app()->homeUrl);
        }
    }

    /*
     * @author Nguyen Van Cuong
     * action change password
     */

    public function actionChangePassword()
    {
        $form = new ChangePasswordForm;
        if (isset($_POST['ChangePasswordForm'])) {
            $form->attributes = $_POST['ChangePasswordForm'];
            $form->validate();
            if (!$form->hasErrors()) {
                $user = $this->current_user;
                if (!$user->isValidPassword($form->oldPass)) {
                    Yii::app()->user->setFlash('error', 'Old password is incorrect!');
                    $this->refresh();
                } else {
                    $user->password = $user->generatePasswordHash($form->newPass);
                    $user->save();
                    Yii::app()->user->setFlash('success', 'Your password has been changed !');
                    $this->redirect(Yii::app()->homeUrl);
                }
            }
        }
        $this->render('change_password', array('form' => $form));
    }
    
    /**
     * @author Nguyen Thi Huyen 
     * @param integer $id of the model to be delete
     */
    public function actionDelete($id) {
        $profile = Profile::model()->findByPk($id);
        if($profile === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        if (!$profile->user->is_admin) {
            $profile->deleteUser();
        }
        $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('/profile/index'));
    }
    
    /*
     * @author Cao Thanh Luc
     * @ fuction test user can acess and can vote
     */
}

?>