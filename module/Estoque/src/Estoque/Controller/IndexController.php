<?php
namespace Estoque\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Estoque\Entity\Produto;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class IndexController extends AbstractActionController
{
	public function indexAction()
	{
		$entityManager  = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$repositorio = $entityManager->getRepository('Estoque\Entity\Produto');

		$produtos = $repositorio->findAll();

		$viewParans = array('produtos' => $produtos);

		return new ViewModel($viewParans);
	}

	public function cadastrarAction()
	{
		if ($this->request->isPost()) {
			
			$nome = $this->request->getPost('nome');
			$preco = $this->request->getPost('preco');
			$descricao = $this->request->getPost('descricao');

			$produtos = new Produto($nome, $preco, $descricao);

			$entityManager  = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$entityManager->persist($produtos);
			$entityManager->flush();

			return $this->redirect()->toUrl('index');
		}

		return new ViewModel();
	}

	public function removerAction()
	{
		$id = $this->params()->fromRoute('id');

		if ($this->request->isPost()) {
			$id = $this->request->getPost('id');

			$entityManager  = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$repositorio = $entityManager->getRepository('Estoque\Entity\Produto');

			$produto = $repositorio->find($id);
			$entityManager->remove($produto);
			$entityManager->flush();

			return $this->redirect()->toUrl('index');
		}

		return new ViewModel(['id' => $id]);
	}

	public function editarAction()
	{

		$id = $this->params()->fromRoute('id');

		if (is_null($id)) {
			$id = $this->request->getPost('id');
		}

		$entityManager  = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$repositorio = $entityManager->getRepository('Estoque\Entity\Produto');

		$produto = $repositorio->find($id);

		if ($this->request->isPost()) {

			$produto->setNome($this->request->getPost('nome'));
			$produto->setPreco($this->request->getPost('preco'));
			$produto->setDescricao($this->request->getPost('descricao'));

			$entityManager->persist($produto);
			$entityManager->flush();

			$this->flashMessenger()->addSuccessMessage('Produto alterado com sucesso!');

			return $this->redirect()->toUrl('index');
		}

		return new ViewModel(['produto' => $produto]);
	}

	public function contatoAction()
	{
		if ($this->request->isPost()) {
			
			$nome = $this->request->getPost('nome');
			$email = $this->request->getPost('email');
			$mensagem = $this->request->getPost('mensagem');

			$msgHtml = "
				<b>Nome: </b> {$nome}, <br />
				<b>Email: </b> {$email}, <br />
				<b>Mensagem: </b> {$mensagem}, <br />
			";

			$htmlPart = new MimePart($msgHtml);
			$htmlPart->type = 'text/html';

			$htmlMsg = new MimeMessage();
			$htmlMsg->addPart($htmlPart);

			$email = new Message();
			$email->addTo('edcesar@edcesar.com');
			$email->setSubject('Contato feito pelo site');
			$email->addFrom('edcesar@edcesar.com');

			$email->setBody($htmlMsg);

			$config = [
			   'host' => 'smtp.gmail.com',
			   'connection_class' => 'login',
			   'connection_config' => [
			     'ssl' => 'tls',
			     'username' => 'meuEmail',
			     'password' => 'minhaSenha'
			   ],
			   'port' => 587
			];

			$transport = new SmtpTransport();
			$options = new SmtpOptions($config);
			$transport->setOptions($options);
			$transport->send($email);

			$this->flashMessenger()->addMessage('E-mail enviado com sucesso!');
			return $this->redirect()->toUrl('index');

		}
		return new ViewModel();
	}
}