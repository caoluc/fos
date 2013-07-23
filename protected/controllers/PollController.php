<?php

class PollController extends Controller
{

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array('create', 'index', 'view', 'my', 'update', 'vote'),
                'users' => array('@'),
            ),
            array(
                'allow',
                'actions' => array('delete'),
                'expression' => '$user->is_admin'
            ),
            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }
    /**
     * @author Nguyen Van Cuong
     * function view detail poll
     */
    public function actionView($id)
    {
        $poll = Poll::model()->findbyAttributes(array('id' => $id));
        $user = $poll->user;
        $choices = $poll->choices;
        $comments = $poll->comments;
        $all_votes = $this->current_user->getAllVotes($poll->id);
        $can_views = $this->current_user->canViewPoll($poll);
        $can_votes = $this->current_user->canVotePoll($poll);
        if ($can_views) {
            $this->render(
                'view',
                array(
                    'poll' => $poll,
                    'user' => $user,
                    'choices' => $choices,
                    'all_votes' => $all_votes,
                    'comments' => $comments,
                    'can_views' => $can_views,
                    'can_votes' => $can_votes,
                )
            );
        } else {
            Yii::app()->user->setFlash('warning','Can not acess');
            $this->redirect(array('index'));
        }
    }       

    /**
     * @author Nguyen Van Cuong
     */
    public function loadModel($id) //Find Poll where id = $id
    {
        $model = Poll::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * @author Nguyen Van Cuong
     */
    public function actionDelete($id) //delete Poll where id = $id
    {
        $poll = $this->loadModel($id);
        $poll->delete();
        $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @author Nguyen Thi Huyen
     */
    public function actionCreate()
    {
        $poll = new Poll;
        if (isset($_POST['Poll'])) {
            $poll->attributes = $_POST['Poll'];
            $poll->user_id = Yii::app()->user->getId();
            if ($poll->save()) {
                Yii::app()->user->setFlash('success', 'You created successfully!');
                $this->redirect(array('choice/index', 'poll_id' => $poll->id));
            }
        }

        $this->render('create', array(
            'poll' => $poll,
        ));
    }

    /**
     *  @author Vu Dang Tung
     */
    public function actionIndex()
    {
        $polls = Poll::model()->canBeSeenBy($this->current_user->id)->findAll();

        $this->render('index', array('polls' => $polls, 'title' => 'All Polls'));
    }

    public function actionMy()
    {
        $polls = $this->current_user->polls;
        $this->render('index', array('polls' => $polls, 'title' => 'My Polls'));
    }

    public function actionUpdate($id) // id of poll
    {
        $model = $this->loadModel($id);
        if (isset($_POST['Poll'])) {
            $model->attributes = $_POST['Poll'];
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Poll is updated !');
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('update', array(
            'poll' => $model,
        ));
    }

    /**
     * @author Nguyen Anh Tien
     */
    public function actionVote($id)
    {
        $poll = Poll::model()->findByPk($id);
        if ($poll != null) {
            // check if user submited form
            if (isset($_POST['choice'])) {
                if ($poll->is_multichoice) {
                    $sumitted_choices = $_POST['choice'];
                } else {
                    $sumitted_choices = array($_POST['choice'] => 1);
                }
                // ensure choice_ids is in poll's choices
                $valid_choice_ids = array();
                foreach ($sumitted_choices as $choice_id => $checked) {
                    if ($poll->hasChoice($choice_id)) {
                        if ($checked) {
                            $valid_choice_ids[] = $choice_id;
                        }
                    } else {
                        // flash invalid choice
                        Yii::app()->user->setFlash('error', 'You selected an invalid choice !');
                        $this->redirect(array('poll/view', 'id' => $id));
                    }
                }
                // delete previous vote record
                $this->current_user->deleteAllVote($poll->id);

                // must contain only one in case of one-choice
                if (!$poll->is_multichoice && count($valid_choice_ids) > 1) {
                    Yii::app()->user->setFlash('error', 'You can select only one  choice !');
                    $this->redirect(array('poll/view', 'id' => $id));
                }

                foreach ($valid_choice_ids as $choice_id) {
                    $vote = new Vote;
                    // ensure current user is voting
                    $vote->user_id = Yii::app()->user->id;
                    $vote->choice_id = $choice_id;
                    $vote->save();
                    // TODO : add notification
                }
           }
           $this->redirect(array('poll/view', 'id' => $id));
        } else {
            $this->redirect(array('poll/index'));
        }
    }
}
?>

