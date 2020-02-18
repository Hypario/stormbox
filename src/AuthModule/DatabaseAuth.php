<?php


namespace App\AuthModule;


use Framework\Auth\Auth;
use Framework\Auth\User;
use Framework\Exception\NoRecordException;
use Framework\Session\SessionInterface;

class DatabaseAuth implements Auth
{

    /**
     * @var UserTable
     */
    private UserTable $userTable;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @var User
     */
    private ?User $user = null;

    public function __construct(UserTable $userTable, SessionInterface $session)
    {
        $this->userTable = $userTable;
        $this->session = $session;
    }

    public function login(string $username, string $password): ?User
    {
        if (empty($username) || empty($password)) {
            return null;
        }

        $user = $this->userTable->findBy('username', $username);
        if ($user && password_verify($password, $user->password)) {
            $this->setUser($user);
            return $user;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?User
    {
        if ($this->user) {
            return $this->user;
        }
        $userId = $this->session->get('auth.user');
        if ($userId) {
            try {
                $this->user = $this->userTable->find($userId);
                return $this->user;
            } catch (NoRecordException $exception) {
                $this->session->delete('auth.user');
                return null;
            }
        }
        return null;
    }

    public function setUser(User $user): void
    {
        $this->session->set('auth.user', $user->id);
        $this->user = $user;
    }

    public function logout()
    {
        $this->session->delete('auth.user');
    }
}
