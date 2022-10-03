# Raicoin Login
Demonstration of Login function to a website using Raicoin send &amp; receive wallet RPC API combining with PHP session.
/JS folder is optional to remote CDN

The script detect if sender and receiver address is coming from the same RAI address of the given random payment code, if payment made then the PHP session will be excecuted via ajax call to create login session.

The login process as follow:

Step 1: Enter your RAI address (as username)

Step 2: Send RAI to your self exactly as payment code given by the program

Step 3. Wait confirmation from RAI network and script to periodically check the payment. If success, the script will redirect to login page. If failed, nothing happened and payment session will be restarted after 20 minutes. 
