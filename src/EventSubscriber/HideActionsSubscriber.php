<?php

namespace App\EventSubscriber;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;

class HideActionsSubscriber implements EventSubscriberInterface
{
    public function onBeforeCrudActionEvent(BeforeCrudActionEvent $event)
    {
        if (!$adminContext = $event->getAdminContext()) {
            return;
        }
        if (!$crudDto = $adminContext->getCrud()) {
            return;
        }
        if ($crudDto->getEntityFqcn() !== Question::class) {
            return;
        }

        // delete action entirely for delete, detail & edit pages because if we're on either of these pages, a question object will be present. If we're on the index page, question object will be null
        $question = $adminContext->getEntity()->getInstance();
        if ($question instanceof Question && $question->getIsApproved()) {
            $crudDto->getActionsConfig()->disableActions([Action::DELETE]);
        }

        // return s the array of actual actions that will be enabled for the current page
        // the methods used in this is different in this events class than it is when calling it in the controller where the helper methods are modifying the Dto. They perform same functions but under different names
        // hides the delete action link from the INDEX page of the QuestionsCrudController
        $actions = $crudDto->getActionsConfig()->getActions();
        if (!$deleteAction = $actions[Action::DELETE] ?? null) {
            return;
        }
        $deleteAction->setDisplayCallable(function(Question $question) {
            return !$question->getIsApproved();
        });

    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeCrudActionEvent::class => 'onBeforeCrudActionEvent',
        ];
    }
}
