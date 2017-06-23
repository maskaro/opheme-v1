<VirtualHost *:80>
	ServerAdmin webmaster@cloudasyougo.com
	ServerName talo.cloudasyougo.com
	ServerAlias house.cloudasyougo.com

	DocumentRoot /var/www/house
	<Directory />
		Options FollowSymLinks
		AllowOverride None
	</Directory>
	<Directory /var/www/house>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride None
		Order allow,deny
		allow from all
	</Directory>

	ErrorLog /var/log/apache2/cloudasyougo.err.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog /var/log/apache2/cloudasyougo.log combined

</VirtualHost>

<VirtualHost *:80>
	ServerAdmin webmaster@cloudasyougo.com
	ServerName opheme.cloudasyougo.com
	ServerAlias ci.opheme.com
  DocumentRoot /opt/opheme/oPheme_UI
  
	<Directory /opt/opheme/oPheme_UI>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride None
		Order allow,deny
		allow from all
	</Directory>  
  ErrorLog /var/log/apache2/opheme.err.log
  CustomLog /var/log/apache2/opheme.log combined
  LogLevel info
</VirtualHost> 

<VirtualHost *:80>
	ServerAdmin webmaster@cloudasyougo.com
	ServerName live.opheme.com
  DocumentRoot /opt/live.opheme.com/oPheme_UI
  
	<Directory /opt/live.opheme.com/oPheme_UI>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride None
		Order allow,deny
		allow from all
	</Directory>  
  ErrorLog /var/log/apache2/live.opheme.com.err.log
  CustomLog /var/log/apache2/live.opheme.com.log combined
  LogLevel info
</VirtualHost> 


<VirtualHost *:80>
  ServerAdmin webmaster@cloudasyougo.com
	ServerName git.cloudasyougo.com
	<Proxy *>
    Order deny,allow
    Allow from all
  </Proxy>
	ProxyPreserveHost On
	# When enabled, this option will pass the Host: line from the incoming request to the proxied host, 
	# instead of the hostname specified in the ProxyPass line.
	ProxyPass / balancer://tomcat/ stickysession=JSESSIONID|jsessionid nofailover=On

  <Proxy balancer://tomcat>
    BalancerMember http://127.0.0.1:8080
  </Proxy>
</VirtualHost>
