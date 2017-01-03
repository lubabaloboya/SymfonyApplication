<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class todolistController extends Controller {

    /**
     * @Route("/", name="todo_list")
     */
    public function listAction() {
        $todos = $this->getDoctrine()
                ->getRepository('AppBundle:Todo')
                ->findAll();
        return $this->render('todo/index.html.twig', array(
                    'todos' => $todos
        ));
    }

    /**
     * @Route("/todos/about", name="todo_about")
     */
    public function aboutAction() {
        return $this->render('todo/about.html.twig');
    }

    /**
     * @Route("/todos/services", name="todo_services")
     */
    public function servicesAction() {
        return $this->render('todo/services.html.twig');
    }

    /**
     * @Route("/todos/contact", name="todo_contact")
     */
    public function contactAction(Request $request) {
             // Create the form according to the FormType created previously.
            // And give the proper parameters
            $form = $this->createForm('AppBundle\Entity\ContactType',null,array(
                // To set the action use $this->generateUrl('route_identifier')
                'action' => $this->generateUrl('todo_contact'),
                'method' => 'POST'
            ));

            if ($request->isMethod('POST')) {
                // Refill the fields in case the form is not valid.
                $form->handleRequest($request);

                if($form->isValid()){
                    // Send mail
                    if($this->sendEmail($form->getData())){

                        // Everything OK, redirect to wherever you want ! :

                        return $this->redirectToRoute('todo_contact');
                    }else{
                        // An error ocurred, handle
                        var_dump("Errooooor :(");
                    }
                }
            }

            return $this->render('todo/contact.html.twig', array(
                'form' => $form->createView()
            ));
        }

        private function sendEmail($data){
            $myappContactMail = 'lubabaloboya@gmail.com';
            $myappContactPassword = 'dubula123';

            // In this case we'll use the ZOHO mail services.
            // If your service is another, then read the following article to know which smpt code to use and which port
            // http://ourcodeworld.com/articles/read/14/swiftmailer-send-mails-from-php-easily-and-effortlessly
            $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465,'ssl')
                ->setUsername($myappContactMail)
                ->setPassword($myappContactPassword);

            $mailer = \Swift_Mailer::newInstance($transport);

            $message = \Swift_Message::newInstance($data["subject"])
            ->setFrom(array($myappContactMail => "Message by ".$data["name"]))
            ->setTo(array(
                $myappContactMail => $myappContactMail
            ))
            ->setBody($data["message"]);

            return $mailer->send($message);
        }
        
    

    /**
     * @Route("/todos/create", name="todo_create")
     */
    public function createAction(Request $request) {
        $todo = new Todo;
        $form = $this->createFormBuilder($todo)
                ->add('name', textType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('category', textType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('description', textareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('priority', ChoiceType::class, array('choices' => array('Low' => 'Low', 'Normal' => 'Normal', 'High' => 'High'), 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('due_date', DateTimeType::class, array('attr' => array('class' => 'formcontrol', 'style' => 'margin-bottom:15px')))
                ->add('save', submitType::class, array('label' => 'Create Todo', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px')))
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();

            $now = new\DateTime('now');

            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($due_date);
            $todo->setCreateDate($now);

            $em = $this->getDoctrine()->getManager();

            $em->persist($todo);
            $em->flush();

            $this->addFlash(
                    'notice', 'Todo Added'
            );

            return $this->redirectToRoute('todo_list');
        }
        return $this->render('todo/create.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/todos/edit/{id}", name="todo_edit")
     */
    public function editAction($id, Request $request) {
        $todo = $this->getDoctrine()
                ->getRepository('AppBundle:Todo')
                ->find($id);
            $now = new\DateTime('now');
            
            $todo->setName($todo->getName());
            $todo->setCategory($todo->getCategory());
            $todo->setDescription($todo->getDescription());
            $todo->setPriority($todo->getPriority());
            $todo->setDueDate($todo->getDueDate());
            $todo->setCreateDate($now);
        
        $form = $this->createFormBuilder($todo)
                ->add('name', textType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('category', textType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('description', textareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('priority', ChoiceType::class, array('choices' => array('Low' => 'Low', 'Normal' => 'Normal', 'High' => 'High'), 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
                ->add('due_date', DateTimeType::class, array('attr' => array('class' => 'formcontrol', 'style' => 'margin-bottom:15px')))
                ->add('save', submitType::class, array('label' => 'Update Todo', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px')))
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();

            $now = new\DateTime('now');
            
            $em = $this->getDoctrine()->getManager();
            $todo = $em->getRepository('AppBundle:Todo')->find($id);
            
            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($due_date);
            $todo->setCreateDate($now);

            $em->flush();

            $this->addFlash(
                    'notice', 'Todo Updated'
            );

            return $this->redirectToRoute('todo_list');
        }
        
        return $this->render('todo/edit.html.twig', array(
                    'todo' => $todo,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/todos/details/{id}", name="todo_details")
     */
    public function deatilsAction($id) {
        $todo = $this->getDoctrine()
                ->getRepository('AppBundle:Todo')
                ->find($id);
        return $this->render('todo/details.html.twig', array(
                    'todo' => $todo
        ));
    }
    
    /**
     * @Route("/todos/delete/{id}", name="todo_delete")
     */
     public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository('AppBundle:Todo')->find($id);
        
        $em->remove($todo);
        $em->flush();
        
        $this->addFlash(
            'notice',
            'Todo Removed'
        );
        
        return $this->redirectToRoute('todo_list');
    }

}
