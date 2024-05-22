<?php
namespace FreePBX\modules\Userman\Api\Rest;
use FreePBX;
use FreePBX\modules\Api\Rest\Base;
class Userman extends Base {
	protected $module = 'userman';
	public function setupRoutes($app) {

		/**
		 * @verb GET
		 * @returns - list of users
		 * @uri /userman/users
		 */
		$freepbx = $this->freepbx;
		$app->get('/users', function ($request, $response, $args) use($freepbx) {
			$users = $freepbx->Userman->getAllUsers();
			$list = [];
			foreach ($users as $user) {
				$user['assigned'] = $freepbx->Userman->getAssignedDevices($user['id']);

				$list[] = $user;
			}
			$response->getBody()->write(json_encode($list));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a userman user
		 * @uri /userman/users/:id
		 */
		$app->get('/users/{id}', function ($request, $response, $args) use($freepbx) {
			FreePBX::Modules()->loadFunctionsInc('userman');
			if ($args['id'] == 'none') {
				/* Don't do that. */
				$response->getBody()->write(json_encode(false));
			}

			$userman = setup_userman();
			$user = false;
			if ($userman) {
				$user = $freepbx->Userman->getUserByUsername($args['id']);
				if ($user) {
					$user['assigned'] = $freepbx->Userman->getAssignedDevices($user['id']);
				}
			}
			$response->getBody()->write(json_encode($user));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - list of extensions
		 * @uri /userman/extensions
		 */
		$app->get('/extensions', function ($request, $response, $args) use($freepbx) {
			$list = [];
			FreePBX::Modules()->loadFunctionsInc('userman');
			$users = $freepbx->Userman->getAllUsers();
			foreach ($users as $user) {
				if ($user['default_extension'] == NULL || $user['default_extension'] == 'none') {
					continue;
				}

				$list[$user['default_extension']] = ["id" => $user['id'], "username" => $user['username'], "description" => $user['description']];
			}
			$response->getBody()->write(json_encode($list));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a userman user
		 * @uri /userman/extensions/:id
		 */
		$app->get('/extensions/{id}', function ($request, $response, $args) use($freepbx) {
			$userman = null;
			FreePBX::Modules()->loadFunctionsInc('userman');
			if ($args['id'] == 'none') {
				/* Don't do that. */
				$response->withJson(false);
			}

			$userman = setup_userman();
			$user = false;
			if ($userman) {
				$user = $freepbx->Userman->getUserByDefaultExtension($args['id']);
				if ($user) {
					$user['assigned'] = $freepbx->Userman->getAssignedDevices($user['id']);
				}
			}
			$response->getBody()->write(json_encode($user));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());
	}
}
