<h1>Poll Detail </h1>
<div style='height:50px;'></div>
<?php
$this->widget('bootstrap.widgets.TbAlert');
?>
<?php
if (Yii::app()->user->is_admin) {
    echo CHtml::button('Delete Poll', array(
        'class' => 'btn btn-danger',
        'submit' => array(
            'poll/delete',
            'id' => $poll->id),
        'confirm' => 'Do you want to delete this poll ?')
    );
}
echo '</span>&nbsp';
if (Yii::app()->user->is_admin || Yii::app()->user->getId() == $user->id) {
    echo CHtml::button('Edit Poll', array(
        'class' => 'btn btn-warning',
        'submit' => array(
            'poll/update',
            'id' => $poll->id)
        )
    );
}
?>
<table class='detail-view table table-striped table-condensed' id='yw1'>
    <tbody>
        <tr class='odd'>
            <th>User</th>
            <td><?php echo $user->username ?></td>
        </tr>
        <tr class='even'>
            <th>Setting</th>
            <td>
                <b> Multi-choice :</b>
                <?php
                if ($poll->is_multichoice == POll::IS_MULTICHOICES_NO) {
                    echo ' No ';
                } else {
                    echo ' Yes ';
                }
                ?>
            </td>
        </tr>
        <tr class='even'>
            <th></th>
            <td>
                <b> Poll Type :</b>
                <?php
                if ($poll->poll_type == Poll::POLL_TYPE_SETTINGS_ANONYMOUS) {
                    echo ' Anonymous ';
                } else {
                    echo ' Non-Anonymous ';
                }
                ?>
            </td>
        </tr>
        <tr class='even'>
            <th></th>
            <td>
                <b>Poll Display :</b>
                <?php
                switch ($poll->display_type) {
                    case Poll::POLL_DISPLAY_SETTINGS_PUBLIC:
                        echo ' Public ';
                        break;
                    case Poll::POLL_DISPLAY_SETTINGS_RESTRICTED:
                        echo ' Restricted ';
                        break;
                    default:
                        echo ' Invited Only ';
                        break;
                }
                ?>
            </td>
        </tr>
        <tr class='odd'>
            <th></th>
            <td>
                <b>Result Display :</b>
                <?php
                switch ($poll->result_display_type) {
                    case Poll::RESULT_DISPLAY_SETTINGS_PUBLIC:
                        echo 'Pulic';
                        break;
                    case Poll::RESULT_DISPLAY_SETTINGS_VOTED_ONLY:
                        echo 'Voted Only';
                        break;
                    default:
                        echo 'Owner Only';
                        break;
                }
                ?>
            </td>
        </tr>
        <tr class='even'>
            <th></th>
            <td>
                <b>Result Details :</b>
                <?php
                if ($poll->result_detail_type == Poll::RESULT_DETAIL_SETTINGS_ALL) {
                    echo ' All ';
                } else {
                    echo ' Only Percentage ';
                }
                ?>
            </td>
        </tr>
        <tr class='odd'>
            <th></th>
            <td>
                <b>Result Show Time :</b>
                <?php
                if ($poll->result_show_time_type == Poll::RESULT_TIME_SETTINGS_AFTER) {
                    echo ' After vote finish';
                } else {
                    echo ' During ';
                }
                ?>
            </td>
        </tr>
        <tr class='even'>
            <th>Question</th>
            <td><?php echo $poll->question ?></td>
        </tr>
        <tr class='odd'>
            <th>Description</th>
            <td><?php echo $poll->description ?></td>
        </tr>
        </tbody>
    </table>
    <form method="post" action="<?php echo Yii::app()->createUrl('poll/vote', array('id' => $poll->id));?>">
        <?php
        /**
        * @author Pham Tri Thai
        * View result vote
        */
        $total_votes = 0;
        foreach ($choices as $c) {
            $votes = $c->votes;
            $total_votes += sizeof($votes);
        }

        foreach ($choices as $c) {
            if ($poll->is_multichoice == 1) {
                echo CHtml::checkBox('choice['.$c->id.']', false, array(
                    'id' => $c->id,
                    'class' => 'cb',
                    'disabled' => !$can_votes
                    )
                );
            } else {
                echo CHtml::radioButton('choice', false, array(
                    'value' => $c->id,
                    'id' => $c->id,
                    'class' => 'cb',
                    'disabled' => !$can_votes
                    )
                );
            }
            $votes = $c->votes;
            if ($total_votes !== 0) {
                $percent = sizeof($votes) * 100 / $total_votes;
            } else {
                $percent = 0;
            }
            echo '<div class="progress progress-striped active bar_choice">';
            echo '<div class="bar bar-warning" style="width: ' . $percent . '%;"></div>';
            echo CHtml::label($c->content, $c->id, 
                array(
                    'class' => 'content_choice'
                ));
            echo '</div>';

            echo sizeof($votes);
            for ($k = 0; $k < sizeof($votes); $k++) {
                echo CHtml::link($votes[$k]->user->username, '', array(
                    'class' => 'user_vote')
                );
            }
            echo "<div class='clear2'></div>";
        }
        
        if ($poll->is_multichoice == 1) {
            echo CHtml::checkBox('new_choice', false, array(
                'id' => '', 
                'class' => 'cb',
                'disabled' => !$can_votes)
            );
        } else {
            echo CHtml::radioButton('choice', false, array(
                'value' => 'max_id_choice + 1',
                'id' => 'max_id_choice + 1',
                'class' => 'cb',
                'disabled' => !$can_votes)
            );
        }
        echo '<div class="progress progress-striped active bar_choice">';
        echo '<div class="bar bar-warning" style="width: 0%;"></div>';
        echo CHtml::textArea('new_choice', '', array(
            'placeholder' => 'type new choice...',
            'class' => 'type_new_choice')
        );
        echo '</div>';
        echo "<div class='clear2'></div>";
        
        if (empty($all_votes)) {
            echo CHtml::button(
                'Vote', 
                array(
                    'class' => 'btn btn-primary',
                    'submit' => array()
                )
            );
        } else {
            foreach ($all_votes as $vote) {
                echo "You voted with \"{$vote->choice->content}\"<br>";
            }
            echo CHtml::button(
                'Re-Vote', 
                array(
                    'class' => 'btn btn-primary',
                    'submit' => array()
                )
            );
        }
        ?>
    </form>
<table>
    <?php
    foreach ($comments as $comment) {
        $user = $comment->user;
    }
    ?>
</table>
