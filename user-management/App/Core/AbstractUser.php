<?php
namespace App\Core;

abstract class AbstractUser {
    protected $id;
    protected $name;
    protected $email;
    protected $password;

    public function __construct($name, $email, $password, $id = null) {
        $this->id       = $id;
        $this->name     = $name;
        $this->email    = $email;
        $this->password = $password; // already hashed when loaded from DB; plain when creating
    }

    public function getId()    { return $this->id; }
    public function getName()  { return $this->name; }
    public function getEmail() { return $this->email; }

    // Force child classes to declare their role
    abstract public function userRole(): string;
}
