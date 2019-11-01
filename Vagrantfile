Vagrant.configure("2") do |config|
  config.vm.define "mysql-server", primary: true, autostart: true do |cf|
    cf.vm.box = "bento/ubuntu-16.04"
    cf.vm.synced_folder "./", "/vagrant", :mount_options => ["dmode=777", "fmode=666"]
    cf.vm.network :forwarded_port, guest: 3306, host: 13307
    cf.vm.network "private_network", ip: "11.22.9.5" 
    cf.vm.provider "virtualbox" do |v|
      v.name = "MySql Server"
      v.customize ["modifyvm", :id, "--memory", "512"]
    end
    $script = <<-SCRIPT
    echo "Updating packages..."
    apt-get -y update >/dev/null 2>&1
    echo "Installing MySQL..."
    apt-get install -y mysql-client >/dev/null 2>&1
    sudo apt-get install debconf-utils -y > /dev/null 2>&1
    debconf-set-selections <<< "mysql-server mysql-server/root_password password password" > /dev/null 2>&1
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password password" > /dev/null 2>&1
    sudo apt-get -y install mysql-server > /dev/null 2>&1
    echo "Making MySql accessible over network..."
    sed -i '43s!127.0.0.1!0.0.0.0!' /etc/mysql/mysql.conf.d/mysqld.cnf
    echo "Restarting MySql Service..."
    service mysql restart
    echo "Updating SQL Permissioins..."
    mysql -u root -ppassword -e "USE mysql;UPDATE user SET host='%' WHERE User='root';GRANT ALL ON *.* TO 'root'@'%';FLUSH PRIVILEGES;" >/dev/null 2>&1
    echo "Done!"
    echo "MySql Credentials: [ Host = 11.22.9.5, Port = 13307, Username = root, Password = password ]"
    SCRIPT
    cf.vm.provision "shell", inline: $script
  end
end