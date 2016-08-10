<?php 

/*
# Passos para instalar o php-ssh2 no servidor do APS
yum install gcc php-devel php-pear libssh2 libssh2-devel make
pecl install -f ssh2
echo extension=ssh2.so > /etc/php.d/ssh2.ini
service httpd restart
php -m | grep ssh2
*/

require_once( realpath(dirname( __FILE__ ))."/../elemental_api/configConsts.php");

class NiceSSH { 
    // SSH Host 
    public $ssh_host; 
    // SSH Port 
    public $ssh_port = 22; 
    // SSH Server Fingerprint 
    public $ssh_server_fp; 
    // SSH Username 
    public $ssh_auth_user; 
    // SSH Public Key File 
    public $ssh_auth_pub; 
    // SSH Private Key File 
    public $ssh_auth_priv; 
    // SSH Private Key Passphrase (null == no passphrase) 
    public $ssh_auth_pass; 
    // SSH Connection 
    public $connection; 
    
    public function __construct($host, $fingerprint, $user, $pubkey=null, $privkey=null, $pass=null) {
        $this->ssh_host = $host;
        $this->ssh_server_fp = $fingerprint;
        $this->ssh_auth_user = $user;
        $this->ssh_auth_pass = $pass;
        $this->ssh_auth_pub = $pubkey;
        $this->ssh_auth_priv = $privkey;
    }

    public function connect() { 
        if (!($this->connection = ssh2_connect($this->ssh_host, $this->ssh_port))) { 
            throw new Exception('Cannot connect to server'); 
        } 
        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX); 
        if (strcmp($this->ssh_server_fp, $fingerprint) !== 0) { 
            throw new Exception("Unable to verify server identity!\n  $fingerprint"); 
        } 
        if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) { 
            throw new Exception('Autentication rejected by server'); 
        } 
    } 
    public function exec($cmd) { 
        if (!($stream = ssh2_exec($this->connection, $cmd))) { 
            throw new Exception('SSH command failed'); 
        } 
        stream_set_blocking($stream, true); 
        $data = ""; 
        while ($buf = fread($stream, 4096)) { 
            $data .= $buf; 
        } 
        fclose($stream); 
        return $data; 
    } 
    public function disconnect() { 
        $this->exec('echo "EXITING" && exit;'); 
        $this->connection = null; 
    } 

    public static function getVODUsage($clientId){
        $host = ConfigConsts::DELTA_HOST;
        $user = ConfigConsts::DELTA_USER;
        $fingerprint = ConfigConsts::DELTA_FINGERPRINT;
        $niceSSH = new NiceSSH($host, $fingerprint, $user, ConfigConsts::SSH_PUBLIC_KEY, ConfigConsts::SSH_PRIVATE_KEY);
        echo "$host, $fingerprint, $user, ConfigConsts::SSH_PUBLIC_KEY, ConfigConsts::SSH_PRIVATE_KEY";
        $niceSSH->connect();
        // Resultados são retornados em KB
        // temos tudo detalhado para o bilhetador logar e totalizar
        $result = array();
        $detail = explode("\n", $niceSSH->exec("du -s ".ConfigConsts::DELTA_VOD_STORAGE_LOCATION."/".$clientId."/*/*/*/*"));
        foreach( $detail as $dir ) {
            $it = explode("\t", $dir);
            if( count($it) < 2 )
                continue;
            $result[] = $it;
        }
        return $result;
    }
    public function __destruct() { 
        $this->disconnect(); 
    }
}
/*
    $data = NiceSSH::getVODUsage("Client_000004");
    $totalKB=0;
    foreach($data as $it) {
        $totalKB += $it[0];
        echo "$it[0];$it[1]\n";
    }
    echo "Total KB = $totalKB\n";
*/
?> 
