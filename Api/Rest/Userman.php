<?php
namespace FreePBX\modules\Userman\Api\Rest;
use FreePBX\modules\Api\Rest\Base;
class Userman extends Base {
	protected $module = 'userman';
	public function setupRoutes($app) {

		/**
		 * @verb GET
		 * @returns - list of users
		 * @uri /userman/users
		 */
		$app->get('/users', function ($request, $response, $args) {
			$users = $this->freepbx->Userman->getAllUsers();
			$list = [];
			foreach ($users as $user) {
				$user['assigned'] = $this->freepbx->Userman->getAssignedDevices($user['id']);

				$list[] = $user;
			}
			return $response->withJson($list);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a userman user
		 * @uri /userman/users/:id
		 */
		$app->get('/users/{id}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('userman');
			if ($args['id'] == 'none') {
				/* Don't do that. */
				$response->withJson(false);
			}

			$userman = setup_userman();
			$user = false;
			if ($userman) {
				$user = $this->freepbx->Userman->getUserByUsername($args['id']);
				if ($user) {
					$user['assigned'] = $this->freepbx->Userman->getAssignedDevices($user['id']);
				}
			}
			return $response->withJson($user);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - list of extensions
		 * @uri /userman/extensions
		 */
		$app->get('/extensions', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('userman');
			$users = $this->freepbx->Userman->getAllUsers();
			foreach ($users as $user) {
				if ($user['default_extension'] == NULL || $user['default_extension'] == 'none') {
					continue;
				}

				$list[$user['default_extension']] = array(
					"id" => $user['id'],
					"username" => $user['username'],
					"description" => $user['description']
				);
			}
			return $response->withJson($list);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a userman user
		 * @uri /userman/extensions/:id
		 */
		$app->get('/extensions/{id}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('userman');
			if ($args['id'] == 'none') {
				/* Don't do that. */
				$response->withJson(false);
			}

			$user = $userman->getUserByDefaultExtension($args['id']);
			if ($user) {
				$user['assigned'] = $userman->getAssignedDevices($user['id']);
			}

			return $response->withJson($user);
		})->add($this->checkAllReadScopeMiddleware());
	}
}
