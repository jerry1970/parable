<?php

namespace Parable\Framework;

class Authentication
{
    /** @var string */
    protected $userClassName = '\Model\User';

    /** @var null|object */
    protected $user;

    /** @var \Parable\Framework\Toolkit */
    protected $toolkit;

    /** @var \Parable\Http\Values\Session */
    protected $session;

    /** @var bool */
    protected $authenticated = false;

    /** @var array */
    protected $authenticationData = [];

    /** @var bool */
    protected $initialized = false;

    public function __construct(
        \Parable\Framework\Toolkit $toolkit,
        \Parable\Http\Values\Session $session
    ) {
        $this->toolkit = $toolkit;
        $this->session = $session;
    }

    /**
     * Initialize the authentication, picking up on session data if possible.
     *
     * @return bool
     */
    public function initialize()
    {
        // If we've already been initialized, we don't need to re-do the logic again.
        if ($this->isInitialized()) {
            return $this->isAuthenticated();
        }

        if ($this->checkAuthentication()) {
            $data = $this->getAuthenticationData();
            if (!isset($data['user_id'])) {
                return false;
            }
            $userId = $data['user_id'];
            $user = $this->toolkit->getRepository($this->userClassName)->getById($userId);
            if (!$user) {
                $this->setAuthenticated(false);
                $this->setAuthenticationData([]);
                return false;
            }
            $this->setUser($user);
            return true;
        }
        return false;
    }

    /**
     * @param $password
     *
     * @return bool|string
     */
    public function generatePasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Checks whether there's an auth session
     *
     * @return bool
     */
    protected function checkAuthentication()
    {
        $authSession = $this->session->get('auth');
        if ($authSession) {
            $this->setAuthenticated($authSession['authenticated']);
            $this->setAuthenticationData($authSession['data']);
            return true;
        }
        return false;
    }

    /**
     * Sets whether there's an authenticated user or not
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setAuthenticated($value = true)
    {
        $this->authenticated = (bool)$value;
        return $this;
    }

    /**
     * Checks whether we've been initialized or not
     *
     * @return bool
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Checks whether there's an authenticated user or not
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Set the data for the user currently authenticated
     *
     * @param array $data
     *
     * @return $this
     */
    public function setAuthenticationData(array $data)
    {
        $this->authenticationData = $data;
        return $this;
    }

    /**
     * Return the authentication data
     *
     * @return array
     */
    public function getAuthenticationData()
    {
        return $this->authenticationData;
    }

    /**
     * @param string $className
     *
     * @return $this
     * @throws \Parable\Framework\Exception
     */
    public function setUserClassName($className)
    {
        try {
            \Parable\DI\Container::create($className);
        } catch (\Exception $e) {
            throw new \Parable\Framework\Exception($this->userClassName . ' could not be instantiated.');
        }

        $this->userClassName = $className;
        return $this;
    }

    /**
     * @param $user
     *
     * @return $this
     * @throws Exception
     */
    public function setUser($user)
    {
        if (!($user instanceof $this->userClassName)) {
            throw new \Parable\Framework\Exception("Invalid object provided, type {$this->userClassName} required.");
        }
        $this->user = $user;
        return $this;
    }

    /**
     * Return the user entity
     *
     * @return null
     */
    public function getUser()
    {
        if (!$this->user) {
            $this->initialize();
        }
        return $this->user;
    }

    /**
     * Check whether the provided password matches the password hash
     *
     * @param string $passwordProvided
     * @param string $passwordHash
     *
     * @return bool
     */
    public function authenticate($passwordProvided, $passwordHash)
    {
        if (password_verify($passwordProvided, $passwordHash)) {
            $this->setAuthenticated(true);
            $this->session->set('auth', [
                'authenticated' => true,
                'data' => $this->authenticationData,
            ]);
        } else {
            $this->revokeAuthentication();
        }
        return $this->isAuthenticated();
    }

    /**
     * Revoke an existing authentication
     *
     * @return $this
     */
    public function revokeAuthentication()
    {
        $this->user = null;
        $this->setAuthenticated(false);
        $this->session->remove('auth');

        return $this;
    }
}