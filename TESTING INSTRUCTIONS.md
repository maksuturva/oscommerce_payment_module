TESTING THE PAYMENT MODULE WITH MAKSUTURVA PAYMENT SERVICE
==========================================================
We encourage you to test the functionalities of the module and how it suits your needs in a good time before the planned release of your webstore. 

Maksuturva / eMaksut -payment modules have been released under the open source GNU LGPL 2.1 licence since 2012 and have had several improvements since. But even though the modules have been tested in Maksuturva's test environment, one should always keep in mind that the licence explicitly states that licence does not guarantee that the module is faultless or that it fits the needs of you or your webstore. Your webstore software might also have functionalities that the payment module does not currently utilize or support. 
GNU LGPL licence allows everyone to modify the module to suit their own needs and the intended platform.

More information

SANDBOX TESTING
---------------
Most simple way to test the payment module is to switch the Sandbox / Testing mode on. In the sandbox mode after confirming the order, the user is directed to a test page where you can see all the passed information and locate possible errors. In the sandbox page you can also test ok-, error-, cancel- and delayed payment -responses that Maksuturva service might send to your service.


TESTING WITH A TEST ACCOUNT
---------------------------
For testing the module with actual internet bank, credit card, invoice or part payment services, you can order a test account for yourself.

http://test1.maksuturva.fi/MerchantSubscriptionBeginning.pmt

When ordering a test account signing the order with your TUPAS bank credentials is not required. When you have completed the order and stored your test account ID and secret key, we kindly ask you to contact us for us to activate the account.

In the test environment no actual money is handled and no orders tracked. Do not try to use actual bank credentials in the test environment. Credentials for test environment can be found at: http://docs.maksuturva.fi/en/html/pages/4_2_personal_test_credentials.html.

For testing our payment service without using actual money, you need to set communication URL in the module configurations as http://test1.maksuturva.fi. All our test environment services are found under that domain unlike our production environment services which are found under SSL-secured domain https://www.maksuturva.fi. Test environment for KauppiasExtranet can be found similarly at http://test1.maksuturva.fi/extranet/.


If sandbox testing passes but testing with test server fails, the reason most likely is in communication URL, seller id or secret key. In that case you should first check that they are correct and no extra spaces are added in the beginning or end of the inputs.


Maksuturva payment service APIs and Integration Guidelines 
-------------------------------
Instructions and manuals for integration can be found at:
Finnish: http://docs.maksuturva.fi/fi/html/pages/
English: http://docs.maksuturva.fi/en/html/pages/

These are found helpful in most cases.

Suomen Maksuturva Oy
8.6.2015


