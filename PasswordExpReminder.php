<?php
namespace FreePBX\modules\Userman;

use FreePBX;
use PDO;
use Exception;
use PDOException;
use ampuser;

#[\AllowDynamicProperties]
class PasswordExpReminder {

    final public const USER_TYPE_ADMIN = 'admin';
    final public const USER_TYPE_UCP = 'ucp';
    final public const NOTIFY_USER_BEFORE_X_DAYS_OF_PASSWORD_EXPIRATION = 5;

    private string $tokenExpiration = "1 day";

	public function __construct()
	{
		$this->FreePBX = FreePBX::create();
		$this->db = $this->FreePBX->Database;
		$this->Userman = $this->FreePBX->Userman;
	}

	public function getSettings($keyword)
	{
		$settings = $this->Userman->getConfig('pwdExpReminder');
		switch ($keyword) {
			case 'forcePasswordReset':
				return !empty($settings["forcePasswordReset"]) ? $settings["forcePasswordReset"] : 0;
				break;
			case 'passwordExpiryReminder':
				return !empty($settings["passwordExpiryReminder"]) ? $settings["passwordExpiryReminder"] : 0;
				break;
			case 'passwordExpirationDays':
				return !empty($settings["passwordExpirationDays"]) ? $settings["passwordExpirationDays"] : 90;
				break;
			case 'passwordExpiryReminderDays':
				return !empty($settings["passwordExpiryReminderDays"]) ? $settings["passwordExpiryReminderDays"] : self::NOTIFY_USER_BEFORE_X_DAYS_OF_PASSWORD_EXPIRATION;
				break;
		}
	}

    public function usermanShowPage()
    {
        $request = freepbxGetSanitizedRequest();
		$isPasswordExpiryReminderEnabledSystemWide = $this->getSettings('passwordExpiryReminder') ?: 0;
		$isForcePasswordResetEnabledSystemWide = $this->getSettings('forcePasswordReset') ?: 0;
		
		if (!empty($request['action']) && ($isPasswordExpiryReminderEnabledSystemWide || $isForcePasswordResetEnabledSystemWide )) {
			switch ($request['action']) {
				case 'showgroup':
					$passexpiry = ($this->Userman->getModuleSettingByGID($request['group'], 'userman', 'passexpiry'));
					$forcePasswordReset = ($this->Userman->getModuleSettingByGID($request['group'], 'userman', 'forcepasswordreset'));
					return [["title" => _("Password Management"), "rawname" => "pwdExpReminder", "content" => load_view(__DIR__ . '/views/password_exp_reminder_hook.php', ["mode" => "group", "passexpiry" => $passexpiry, 'forcePasswordReset' => $forcePasswordReset, 'isNewUser' => false, 'action' => $request['action'], 'isPasswordExpiryReminderEnabledSystemWide' => $isPasswordExpiryReminderEnabledSystemWide, 'isForcePasswordResetEnabledSystemWide' => $isForcePasswordResetEnabledSystemWide])]];
					break;
				case 'showuser':
					$passexpiry = $this->Userman->getModuleSettingByID($request['user'], 'userman', 'passexpiry', true);
                    $isNewUser = $this->Userman->getModuleSettingByID($request['user'], 'userman', 'isNewUser',  true);
                    if ($isPasswordExpiryReminderEnabledSystemWide) {
                        return [["title" => _("Password Management"), "rawname" => "pwdExpReminder", "content" => load_view(__DIR__ . '/views/password_exp_reminder_hook.php', ["mode" => "user", "passexpiry" => $passexpiry, 'isNewUser' => $isNewUser, 'action' => $request['action'], 'isPasswordExpiryReminderEnabledSystemWide' => $isPasswordExpiryReminderEnabledSystemWide, 'isForcePasswordResetEnabledSystemWide' => $isForcePasswordResetEnabledSystemWide])]];
                    }
					break;
				case 'addgroup':
				case 'adduser':
					$mode = ($request['action'] == 'addgroup') ? 'group' : 'user';
					return [["title" => _("Password Management"), "rawname" => "pwdExpReminder", "content" => load_view(__DIR__ . '/views/password_exp_reminder_hook.php', ["mode" => $mode, 'isNewUser' => $request['action'] == 'adduser' ? 1 : 0, 'action' => $request['action'], 'isPasswordExpiryReminderEnabledSystemWide' => $isPasswordExpiryReminderEnabledSystemWide, 'isForcePasswordResetEnabledSystemWide' => $isForcePasswordResetEnabledSystemWide])]];
					break;
			}
		}
    }

    public function usermanAddGroup($id, $display, $data)
	{
		$this->usermanUpdateGroup($id, $display, $data);
	}

	public function usermanUpdateGroup($id, $display, $data)
	{
		if ($display == 'userman' && !empty($_POST['type']) && $_POST['type'] == 'group') {
			if (isset($_POST['passexpiry_enable'])) {
				if ($_POST['passexpiry_enable'] == 'true') {
					$this->Userman->setModuleSettingByGID($id, 'userman', 'passexpiry', true);
				} else {
					$this->Userman->setModuleSettingByGID($id, 'userman', 'passexpiry', false);
				}
			}
			if (isset($_POST['forcePasswordReset'])) {
				if ($_POST['forcePasswordReset'] == '1') {
					$this->Userman->setModuleSettingByGID($id, 'userman', 'forcepasswordreset', true);
				} else {
					$this->Userman->setModuleSettingByGID($id, 'userman', 'forcepasswordreset', false);
				}
			}
			$group = $this->Userman->getGroupByGID($id);
			foreach ($group['users'] as $user) {
				$data = $this->Userman->getUserByID($user);
                $this->usermanUpdateUser($user, $display, $data, true);
			}
		}
	}

	public function usermanAddUser($id, $display, $data)
	{
        $this->Userman->setModuleSettingByID($id, 'userman', 'isNewUser',  true);
        $this->usermanUpdateUser($id, $display, $data, false);
	}

    public function usermanUpdateUser($id, $display, $data, $group = false)
	{
        $passData = [];
        $passExpData = [];
        $isNewUser = isset($_POST['isNewUser']) ? ($_POST['isNewUser'] ? 1 : 0) : $this->Userman->getModuleSettingByID($data['id'], 'userman', 'isNewUser',  true);
		$user = $this->Userman->getUserByID($id);

		# Updating Password Expiry Settings
		$passwordExpiryField = !empty($_POST['passexpiry_enable']) ? $_POST['passexpiry_enable'] : 0; 
		if (isset($passwordExpiryField)) {
			$this->Userman->setModuleSettingByID($id, 'userman', 'passexpiry',  $passwordExpiryField == 'inherit' ? null : ($passwordExpiryField  == 'true' ? 1 : 0));
			if($isNewUser ){
				$passData['id'] = $id;
				$passData['username'] = $user['username'] ?? '';
				$passData['email'] = $user['email'] ?? '';
				$this->resetPasswordExpiry($passData, 'ucp', 1);
			}else{
				if($passwordExpiryField == 'inherit'){
					$enabled = $this->Userman->getCombinedModuleSettingByID($id, 'userman', 'passexpiry');
				}else if($passwordExpiryField  == 'true'){
					$enabled = 1;
				}else{
					$enabled = 0;
				}
				$passExpData['uid'] = $id; 
                $passExpData['username'] = $user['username']; 
                $passExpData['status'] = $enabled ? 'enable' : 'disable'; 
                $passExpData['usertype'] = 'ucp';
				$this->enableOrDisablePasswordReminder($passExpData);
			}
			
			$this->Userman->setModuleSettingByID($id, 'userman', 'passexpiry',  $passwordExpiryField == 'inherit' ? null : ($passwordExpiryField  == 'true' ? 1 : 0));
		}

		# Updating Force Password Reset Settings
		$forcePasswordResetField = !empty($_POST['forcePasswordReset']) ? $_POST['forcePasswordReset'] : 0; 
		if (!empty($forcePasswordResetField)) {
			$isForcePasswordResetEnabled = 0;
			if($forcePasswordResetField == 'inherit'){
				$isForcePasswordResetEnabled = null;
			}else{
				$isForcePasswordResetEnabled = $isNewUser && $forcePasswordResetField == 1 ? 1 : 0; 
			}
			$this->Userman->setModuleSettingByID($id, 'userman', 'forcepasswordreset',  $isForcePasswordResetEnabled);
		}
		$this->Userman->setModuleSettingByID($id, 'userman', 'isNewUser',  $isNewUser);

	}

	public function usermanDeleteUser($id, $display, $data)
	{
		if(isset($data['username'])){
			$this->deleteReminderSettingsForUser($data['username'],$id);
		}
	}

    public function setPaswordConfig($request)
    {
        $oldPasswordExpiryReminder = $this->getSettings("passwordExpiryReminder");
        $oldPasswordExpirationDays = $this->getSettings("passwordExpirationDays");
        $forcePasswordReset = filter_var(!empty($request['forcePasswordReset']) ? $request['forcePasswordReset'] : 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $passwordExpiryReminder = filter_var(!empty($request['passwordExpiryReminder']) ? $request['passwordExpiryReminder'] : 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $passwordExpirationDays = filter_var(!empty($request['passwordExpirationDays']) ? $request['passwordExpirationDays'] : 90, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $passwordExpiryReminderDays = filter_var(!empty($request['passwordExpiryReminderDays']) ? $request['passwordExpiryReminderDays'] : self::NOTIFY_USER_BEFORE_X_DAYS_OF_PASSWORD_EXPIRATION, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $passwordSettings = [
            'forcePasswordReset' => $forcePasswordReset,
            'passwordExpiryReminder' => $passwordExpiryReminder,
            'passwordExpirationDays' => $passwordExpirationDays,
            'passwordExpiryReminderDays' => $passwordExpiryReminderDays
        ];
        $this->Userman->setConfig('pwdExpReminder', $passwordSettings);
        $allGroups = $this->Userman->getAllGroups();

        if($oldPasswordExpiryReminder != $passwordExpiryReminder || $oldPasswordExpirationDays != $passwordExpirationDays){

            $updatePassExpiryOnly = $oldPasswordExpirationDays != $passwordExpirationDays ? true : false;

            # Update password expiry table only when settings changed
    
            # Enable / Disable Password Reminder option and Force Password Reset for all Groups
            foreach ($allGroups as $group) {
                $this->Userman->setModuleSettingByGID($group['id'], 'userman', 'forcepasswordreset', $forcePasswordReset ? 1 : 0);
                $this->Userman->setModuleSettingByGID($group['id'], 'userman', 'passexpiry', $passwordExpiryReminder ? 1 : 0);
            }

            # Enable / Disable Password Reminder option for all users
            $ucpUsers = $this->getAllUsers(self::USER_TYPE_UCP);
            $adminUsers = $this->getAllUsers(self::USER_TYPE_ADMIN);
        
            foreach ($ucpUsers as $user) {
                $data['uid'] =$user['id']; 
                $data['username'] = $user['username']; 
                $data['status'] = $passwordExpiryReminder ? 'enable' : 'disable'; 
                $data['usertype'] = self::USER_TYPE_UCP; 
                $data['updatePassExpiryOnly'] = $updatePassExpiryOnly;
                $this->enableOrDisablePasswordReminder($data);
            }

            foreach ($adminUsers as $user) {
                $data['uid'] = ""; 
                $data['username'] = $user['username']; 
                $data['status'] = $passwordExpiryReminder ? 'enable' : 'disable'; 
                $data['usertype'] = self::USER_TYPE_ADMIN; 
                $data['updatePassExpiryOnly'] = $updatePassExpiryOnly;
                $this->enableOrDisablePasswordReminder($data);
            }
        
        }
    }

    public function getAllUsers($userType){
        if($userType == self::USER_TYPE_UCP){
            $sql = 'SELECT * from userman_users';
        }else{
            $sql = 'SELECT * from ampusers';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkPasswordReminder($request)
    {
        $response = [];
        $response['status'] = true;
        $response['message'] = "";
        $username = filter_var($request['username'] ?? '',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_var($request['password'] ?? '',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $loginpanel = filter_var($request['loginpanel'] ?? 'admin',FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Decoding Password
        $password = base64_decode(urldecode((string) $password));
        
        # check whether session is already unlocked
        $isSessionAlreadyUnlocked = false;
        if ($loginpanel == self::USER_TYPE_ADMIN) {
            $isSessionAlreadyUnlocked = isset($_SESSION['AMP_user']) ? true : false;
        } else if ($loginpanel == self::USER_TYPE_UCP) {
            $isSessionAlreadyUnlocked = isset($_SESSION['UCP_token']) ? true : false;
        }

        if ($isSessionAlreadyUnlocked) {
            return ['status' => false, 'isSessionAlreadyUnlocked' => true];
        }

        # Check this user is a UCP user or admin user because ucp users can also login to admin panel
        $res = $this->isUCPUser($username);
        if ($res['status']) {
            $usertype = self::USER_TYPE_UCP;
        } else {
            $usertype = self::USER_TYPE_ADMIN;
        }
        
        $response['usertype'] = $usertype;

        if ($loginpanel == 'ucp' && $usertype == self::USER_TYPE_ADMIN) {
            $this->logAuthFailure($username);
            return ['status' => false, 'isSessionAlreadyUnlocked' => false, 'message' =>  _('Invalid Credentials')];
        }

        # Check for correct credentials
        $status = false;
        if ($usertype == self::USER_TYPE_ADMIN) {
            $status = $this->checkAdminCredentials($username, $password);
        } else if ($usertype == self::USER_TYPE_UCP) {
            $status = $this->Userman->checkCredentials($username, $password);
        }

        if ($status) {
            // Check Force Password Reset Settings
            $isForcePasswordResetEnabledSystemWide = $this->getSettings("forcePasswordReset");
            if($isForcePasswordResetEnabledSystemWide){
                if ($usertype == self::USER_TYPE_UCP) {
                    if(isset($res['uid'])){
                        $isNewUser = $this->Userman->getModuleSettingByID($res['uid'], 'userman', 'isNewUser',  true);
                        $needForcePasswordReset =$this->Userman->getModuleSettingByID($res['uid'], 'userman', 'forcepasswordreset',  true);
                        if (is_null($needForcePasswordReset)) {
                            # if Force Password Reset settings for this user is set as inherit (null) then check group settings
                            $needForcePasswordReset = $this->getUserModuleSettingsByGroup($res['uid'], 'userman', 'forcepasswordreset') ? 1 : 0;
                        }
                        if($needForcePasswordReset && $isNewUser){
                            $token = $this->Userman->generatePasswordResetToken($res['uid'], null, true);
                            if ($token) {
                                $response['status'] = false;
                                $response['mustresetpassword'] = true;
                                $response['message'] = _('You must reset your password before continuing');
                                $response['resetlink'] = $this->FreePBX->Ucp->getUcpLink() . "/?forgot=" . $token['token'];
                                return $response;
                            }
                        }
                    }
                }
            }

            // Get Password Reminder Config
            $pwdExpReminderConfig = $this->Userman->getConfig("pwdExpReminder");

            $sql = "SELECT uid, usermail, usertype, passwordChangedAt, passwordExpiryDate from userman_password_reminder where username=:username and usertype=:usertype";
            $sth = $this->db->prepare($sql);
            $sth->execute([
                ":username" => $username,
                ":usertype" => $usertype
            ]);
            $results = $sth->fetch(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $uid = $results['uid'] ?? '';
                $passwordChangedAt = $results['passwordChangedAt'] ?? '';
                $passwordExpirationDays = $pwdExpReminderConfig['passwordExpirationDays'] ?? '';
                $passwordExpiryReminderDays = $pwdExpReminderConfig['passwordExpiryReminderDays'] ?? self::NOTIFY_USER_BEFORE_X_DAYS_OF_PASSWORD_EXPIRATION;
                $currentDatetime = strtotime(date("Y-m-d H:i:s"));
                $passwordExpiryDate = strtotime($passwordChangedAt . ' + ' . $passwordExpirationDays . ' days');

                if ($passwordExpiryDate != '' && $currentDatetime > $passwordExpiryDate) {
                    $response['status'] = false;

                    if ($currentDatetime > $passwordExpiryDate) {
                        $response['mustresetpassword'] = true;
                        $response['message'] = _('You must reset your password before continuing');

                        if ($usertype == self::USER_TYPE_UCP) {
                            $token = $this->Userman->generatePasswordResetToken($uid, null, true);
                            if ($token) {
                                $response['resetlink'] = $this->FreePBX->Ucp->getUcpLink() . "/?forgot=" . $token['token'];
                            }
                        } else {
                            $response['resetPasswordToken'] = $this->generateAdminPasswordResetToken($username);
                        }
                    } else {
                        $datediff = round(($passwordExpiryDate - $currentDatetime) / 86400);  # 86400 seconds * days
                        $response['message'] = sprintf(_('Your password will expire in %s day(s). So, please change your password as soon as possible.'), $datediff);
                    }
                } else {
                    $datediff = round(($passwordExpiryDate - $currentDatetime) / 86400);  # 86400 seconds * days
                    if ($currentDatetime < $passwordExpiryDate && ($datediff <= $passwordExpiryReminderDays)) {
                        $response['status'] = false;
                        if($datediff == 0){
                            $response['message'] = sprintf(_('Your password will expire today. So, please change your password as soon as possible.'), $datediff);
                        }else{
                            $response['message'] = sprintf(_('Your password will expire in %s day(s). So, please change your password as soon as possible.'), $datediff);
                        }
                    }
                }
            }
        } else {
            $this->logAuthFailure($username);
            $response['loginfailed'] = true;
            $response['message'] = _('Invalid Login Credentials');
        }

        return $response;
    }

    public function resetPasswordExpiry($user, $userType = self::USER_TYPE_UCP, $isNewUser = 0, $updatePassExpiryOnly = false)
    {
        try {
            $uid = $user['id'] ?? '';
            $username = $user['username'] ?? '';
            $usermail = $user['email'] ?? '';
            $pwdExpReminderConfig = $this->Userman->getConfig("pwdExpReminder");
            $passwordExpirationDays = $pwdExpReminderConfig['passwordExpirationDays'] ?? 0;
            $isPasswordExpiryReminderEnabledSystemWide = $pwdExpReminderConfig['passwordExpiryReminder'] ?? 0;
            $passwordChangedAt = date('Y-m-d H:i:s');
            $passwordExpiryDate = date('Y-m-d H:i:s', strtotime($passwordChangedAt . ' + ' . $passwordExpirationDays . ' days'));
    
            if ($userType == self::USER_TYPE_UCP) {
                if(!$isNewUser){
                    # Update user is not new user
                    $this->Userman->setModuleSettingByID($uid, 'userman', 'isNewUser',  null);
                }
               
                $isPassExpiryEnabled = $this->Userman->getModuleSettingByID($uid, 'userman', 'passexpiry', true);
                if (is_null($isPassExpiryEnabled)) {
                    # if password expiry settings for this user is set as inherit (null) then check group settings
                    $isPassExpiryEnabled = $this->getUserModuleSettingsByGroup($uid, 'userman', 'passexpiry');
                }
            } else {
                # For All Admins Password Expiry will be enabled 
                $isPassExpiryEnabled = true;
            }
    
            if ($isPassExpiryEnabled && $isPasswordExpiryReminderEnabledSystemWide) {
                if ($userType == self::USER_TYPE_UCP) {
    
                    // Check Password Reminder Exists if ucp
                    $sql = "SELECT * from userman_password_reminder WHERE uid=:uid";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':uid', $uid);
                    $stmt->execute();
                    $userPwdReminderExists = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
    
                    // Check Password Reminder Exists if admin
                    $sql = "SELECT * from userman_password_reminder WHERE username=:username";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':username', $username);
                    $stmt->execute();
                    $userPwdReminderExists = $stmt->fetch(PDO::FETCH_ASSOC);
                }
    
                // If exists update password reminder data else add
                if (!empty($userPwdReminderExists)) {
                    if($updatePassExpiryOnly){
                        $passwordChangedAt = $userPwdReminderExists['passwordChangedAt'];
                        $passwordExpiryDate = date('Y-m-d H:i:s', strtotime($passwordChangedAt . ' + ' . $passwordExpirationDays . ' days'));
                    }
                    if ($userType == self::USER_TYPE_UCP) {
    
                        $sql = "UPDATE userman_password_reminder SET passwordChangedAt=:passwordChangedAt, passwordExpiryDate=:passwordExpiryDate, usermail=:usermail, username=:username WHERE uid= :uid";
                        $sth = $this->db->prepare($sql);
                        $sth->bindParam(':uid', $user['id']);
                        $sth->bindParam(':username', $username);
                        $sth->bindParam(':usermail', $usermail);
                        $sth->bindParam(':passwordChangedAt', $passwordChangedAt);
                        $sth->bindParam(':passwordExpiryDate', $passwordExpiryDate);
                        $sth->execute();
                    } else {
    
                        $sql = "UPDATE userman_password_reminder SET passwordChangedAt=:passwordChangedAt, passwordExpiryDate=:passwordExpiryDate, usermail=:usermail WHERE username=:username";
                        $sth = $this->db->prepare($sql);
                        $sth->bindParam(':username', $username);
                        $sth->bindParam(':usermail', $usermail);
                        $sth->bindParam(':passwordChangedAt', $passwordChangedAt);
                        $sth->bindParam(':passwordExpiryDate', $passwordExpiryDate);
                        $sth->execute();
                    }
                } else {
                    $sql = "INSERT INTO userman_password_reminder (uid, usermail, usertype, passwordChangedAt, passwordExpiryDate, username) VALUES  (:uid, :usermail, :usertype, :passwordChangedAt, :passwordExpiryDate, :username)";
                    $insert = [
                        ':uid' => $uid,
                        ':username' => $username,
                        ':usermail' => $usermail,
                        ':usertype' => $userType,
                        ':passwordChangedAt' => $passwordChangedAt,
                        ':passwordExpiryDate' => $passwordExpiryDate
                    ];
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($insert);
                }
            }else{
                $sql = "DELETE FROM userman_password_reminder WHERE username = :username AND usertype = :usertype";
                $values = [
                    ':username' => $username,
                    ':usertype' => $userType
                ];
                $stmt = $this->db->prepare($sql);
                $stmt->execute($values);
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => _($e->getMessage())];
        }
    }

    public function deletePasswordReminder($usertype, $username)
    {
        try{
            $sql = "DELETE FROM userman_password_reminder WHERE usertype=:usertype and username=:username";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(':usertype', $usertype);
            $sth->bindParam(':username', $username);
            $sth->execute();
        } catch (Exception $e) {
            return ['status' => false, 'message' => _($e->getMessage())];
        }
    }

    public function enableOrDisablePasswordReminder($request)
    {
        $data = [];
        try {
            $uid = filter_var($request['uid'] ?? false,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $username = filter_var($request['username'] ?? '',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $enableStatus = filter_var($request['status'] ?? 'disable',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $usertype = filter_var($request['usertype'] ?? self::USER_TYPE_ADMIN,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $updatePassExpiryOnly = filter_var($request['updatePassExpiryOnly'] ?? false,FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($usertype == self::USER_TYPE_ADMIN) {
                // Do Nothing
            } else if ($usertype == self::USER_TYPE_UCP) {
                // Enable Password Expiry Settings for user groups
                $this->Userman->setModuleSettingByID($uid, 'userman', 'passexpiry', null);
            }

            if ($enableStatus == 'enable') {
                $data['username'] = $username;
                $data['id'] = $uid;
                $this->resetPasswordExpiry($data, $usertype, 0, $updatePassExpiryOnly);
            } else {
                $this->deletePasswordReminder($usertype, $username);
            }

            return ['status' => true, 'message' => _("Password expiry reminder Settings updated successfully")];
        } catch (Exception $e) {
            return ['status' => false, 'message' => _($e->getMessage())];
        }
    }

    /**---------------------------------------------------------------------------------------------------------------------------------------
     *  Admin Reset Password Functions
     *---------------------------------------------------------------------------------------------------------------------------------------/

    /**
     * Get all password reset tokens
     */
    public function getAdminPasswordResetTokens()
    {
        $tokens = $this->Userman->getConfig('adminpassresettoken');
        $final = [];
        $time = time();
        if (!empty($tokens)) {
            foreach ($tokens as $token => $data) {
                if (!empty($data['time']) &&  $data['valid'] < $time) {
                    continue;
                }
                $final[$token] = $data;
            }
        }
        $this->Userman->setConfig('adminpassresettoken', $final);
        return $final;
    }

    /**
     * Reset all password tokens
     */
    public function resetAllPasswordTokens()
    {
        $this->Userman->setConfig('adminpassresettoken', []);
    }

    /**
     * Generate a password reset token for a user
     * @param string $username The user username
     */
    public function generateAdminPasswordResetToken($username)
    {
        $user = $this->getAdminUserByUsername($username);
        $time = time();
        $valid = $this->tokenExpiration;
        if (!empty($user)) {
            $tokens = $this->getAdminPasswordResetTokens();
            if (empty($tokens) || !is_array($tokens)) {
                $tokens = [];
            }
            // If token already exists then remove it
            foreach ($tokens as $token => $data) {
                if ($data['username'] == $username) {
                    unset($tokens[$token]);
                }
            }
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $tokens[$token] = ["username" => $username, "time" => $time, "valid" => strtotime((string) $valid, $time)];
            $this->Userman->setConfig('adminpassresettoken', $tokens);
            return ["token" => $token, "valid" => strtotime((string) $valid, $time)];
        }
        return false;
    }

    /**
     * Validate Password Reset token
     * @param string $token The token
     */
    public function validateAdminPasswordResetToken($token)
    {
        $tokens = $this->getAdminPasswordResetTokens();
        if (empty($tokens) || !is_array($tokens)) {
            return false;
        }
        if (isset($tokens[$token])) {
            $user = $this->getAdminUserByUsername($tokens[$token]['username']);
            if (!empty($user)) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Reset password for a user base on token
     * then invalidates the token
     * @param string $token       The token
     * @param string $newpassword The password
     */
    public function resetAdminPasswordWithToken($request)
    {
        # Form Validation
        if (!isset($request['token']) || empty($request['token'])) {
            return ['status' => false, 'message' => _('Invalid Token')];
        }

        if (!isset($request['newpassword']) || empty($request['newpassword'])) {
            return ['status' => false, 'message' => _('Password is required')];
        }

        if (!isset($request['confirmpassword']) || empty($request['confirmpassword'])) {
            return ['status' => false, 'message' => _('Confirm password is required')];
        }

        if ($request['confirmpassword'] != $request['newpassword']) {
            return ['status' => false, 'message' => _('Confirm password does not match')];
        }

        $token = $request['token'];
        $token = filter_var($token['token'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $newpassword = filter_var($request['newpassword'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $user = $this->validateAdminPasswordResetToken($token);
        if (!empty($user)) {
            $tokens = $this->Userman->getConfig('adminpassresettoken');
            unset($tokens[$token]);
            $this->Userman->setConfig('adminpassresettoken', $tokens);
            $status = $this->updateAdminUserPassword($user['username'], $newpassword);
            if ($status) {
                // If user is logged in using fwconsole unlock then force logout
                unset($_SESSION['AMP_user']);
                return ['status' => true, 'message' => _('Your password has been reset successfully')];
            } else {
                return ['status' => false, 'message' => _('Not able to reset password')];
            }
        } else {
            return ['status' => false, 'message' => _('Not able to reset password')];
        }
    }

    public function getAdminUserByUsername($username)
    {
        if (empty($username)) {
            return false;
        }
        try {
            $sql = "SELECT * FROM ampusers WHERE username=:username";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function updateAdminUserPassword($username, $password)
    {
        $data = [];
        if (empty($username) || empty($password)) {
            return false;
        }
        try {
            $pass = sha1((string) $password);
            $sql  = "UPDATE ampusers SET password_sha1=:passwordsha1 WHERE username=:username";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':passwordsha1', $pass);
            $result =  $stmt->execute();
            if ($result) {
                $data['username'] = $username;
                $userType = self::USER_TYPE_ADMIN;
                $this->resetPasswordExpiry($data, $userType);
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function clearAllEnabledUsersSettings()
    {
        # Disable settings for UCP users
        $sql = 'DELETE from userman_users_settings WHERE userman_users_settings.key = "passexpiry"';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $sql = 'DELETE from userman_groups_settings WHERE userman_groups_settings.key = "passexpiry"';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function deleteReminderSettingsForUser($username, $uid)
    {
        $sql = "DELETE from userman_password_reminder where username = :username and uid = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':uid', $uid);
        $stmt->execute();
    }
    
	public function isUCPUser($username)
    {
		$user = $this->Userman->getUserByUsername($username);
		if (!empty($user)) {
			return ['status' => true, 'uid' => $user['id']];
		}else{
			return ['status' => false];
		}
	}

    public function getRemoteIp()
    {
        $return = false;
        $return = match (true) {
            !empty ($_SERVER['HTTP_X_REAL_IP']) => $_SERVER['HTTP_X_REAL_IP'],
            !empty ($_SERVER['HTTP_CLIENT_IP']) => $_SERVER['HTTP_CLIENT_IP'],
            !empty ($_SERVER['HTTP_X_FORWARDED_FOR']) => $_SERVER['HTTP_X_FORWARDED_FOR'],
            default => $_SERVER['REMOTE_ADDR'],
        };
		//Return the IP or false if it is invalid or local
		return filter_var($return, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
	}

	public function logAuthFailure($username)
	{
		# IF Invalid Credentials add details to log
		$ip = $this->getRemoteIp();
		$logMessage = 'Authentication failure for ' . (!empty($username) ? $username : 'unknown') . ' from ' . $_SERVER['REMOTE_ADDR'];
		freepbx_log_security($logMessage);
		if ($ip !== $_SERVER['REMOTE_ADDR']) {
			freepbx_log_security('Possible proxy detected, forwarded headers for' . (!empty($username) ? $username : 'unknown') . ' set to ' . $ip);
		}
		$logData = [];
	}

	public function checkAdminCredentials($username, $password)
	{
		if (empty($username) || empty($password)) {
			return false;
		}
		try {
            # Check logged in user is from usermanager 
            $isValidCredentials = false;
            $ampUser = new ampuser($username, "usermanager");
            if ($ampUser->checkPassword($password)) {
                if ($this->Userman->getCombinedGlobalSettingByID($ampUser->id, 'pbx_login')) {
                    $isValidCredentials = true;
                }
            }else{

				# Check logged in user is from admin users 
				$isValidCredentials = false;
				$ampUser = new ampuser($username);
				if ($ampUser->checkPassword($password)) {
					$isValidCredentials = true;
				}
            
			}
         
            return $isValidCredentials;

		} catch (PDOException $e) {
			throw $e;
		}
	}

	public function getUserModuleSettingsByGroup($id,$module,$setting)
    {
		$val = false;
		$groups = $this->Userman->getGroupsByID($id);
		foreach($groups as $group) {
			$gs = $this->Userman->getModuleSettingByGID($group, $module, $setting,true,false);
			if(!is_null($gs)) {
				$val = $gs;
				break;
			}
		}
		return $val;
	}
    
}