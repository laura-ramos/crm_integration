# CRM Integration
### CRM Integration with Bigin CRM

#### Register your application
Before you make requests to Bigin API endpoints, register your application (client) with Bigin's Authorization server.
- **Step 1: Open the API console**
    Go to Zoho API Console https://api-console.zoho.com/.
- **Step 2: Select a client type**
    If you are registering an application for the first time, click GET STARTED.
    If you already registered an application and want to register a new one, then click + ADD CLIENT.
    The client type is the type of application you build.
- **Step 3: Fill out client details**
    Based on the selected client type, you must provide the following details and then click CREATE:

    **Client Name:** Type the name of the application.
    **Homepage URL:** Provide the home page URL of your application.
    **Authorized Redirect URIs:** Provide a webpage URL of your application to which the accounts URL redirects you with an authorization code after the user's successful validation.
    **Javascript Domain:** Provide a Javascript Domain URL of your application.
- **Step 4: Copy the client ID and secret**
    Once the registration of your application is complete, you will receive the following credentials that are used to identify your registered application:
    **Client ID:** A unique identifier that contains the registration information of an application. The authorization server identifies the application using this client identifier.
    **Client Secret:** A unique key that helps authenticate an application with the authorization server. The client secret is privy to the application and authorization server and must be kept safe.
- **Step 5: (Optional) Configure multi-dc for the client**
    You can now set up the multi-dc configuration for your application. The multi-dc configuration allows you to control the users from different domains to access your application.
    To set up multi-dc configuration, in the API console, select the application you want to access and then navigate to the Settings tab. On the Settings tab, from the list of data centers, enable the slider for the data center you want to allow to access your application.

#### Configuration