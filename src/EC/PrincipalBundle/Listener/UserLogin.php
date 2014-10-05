<?php  
    
namespace EC\PrincipalBundle\Listener;  
    
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;  
use EC\PrincipalBundle\Entity\Usuario; 
use EC\PrincipalBundle\Entity\Log;  
    
class UserLogin  {  

    protected $doctrine;  
    protected $container;  
    
    public function __construct(\Doctrine\Bundle\DoctrineBundle\Registry $doctrine, $container )  
    {  
    	$this->doctrine = $doctrine;  
    	$this->container = $container;  
    }  
    
    public function onLogin(InteractiveLoginEvent $event)  {  
      $entityManager = $this->doctrine->getManager();  
    	$user = $this->container->get('security.context')->getToken()->getUser(); // getting the user  
    	$ip= $this->container->get('request')->getClientIp();
    	
    	$log=new Log();
    	$log->setUsuario($user);
    	$log->setIp($ip);
    	$user->addLog($log);
 
 		$entityManager->persist($user);
 		$entityManager->persist($log);
		$entityManager->flush(); //saving data  
    }  
}  