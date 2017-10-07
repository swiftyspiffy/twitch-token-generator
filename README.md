# TwitchTokenGenerator.com
[https://twitchtokengenerator.com](https://twitchtokengenerator.com)

![Image of site](http://i.imgur.com/boWfK8h.png)

### Overview
A simple tool to generate access tokens for Twitch with custom scopes. Good tool for testing various Twitch third party tools (like [swiftyspiffy/TwitchLib](https://github.com/swiftyspiffy/twitchlib)).
- Full Site: [twitchtokengenerator.com](https://twitchtokengenerator.com)
- Mobile Site: [twitchtokengenerator.com/mobile](https://twitchtokengenerator.com/mobile)

### API
An API exists on TwitchTokenGenerator allowing the creation of tokens and implementation in applications. The API is currently implemented in TwitchLib. A flow of how the API works is listed below:

1. Ping the create endpoint to get a link to give to user:
 - Create Endpoint: `https://twitchtokengenerator.com/api/create`
 - Required Rarameters:
 
 - - base64 encoded application title
  
 - - scope list with + delimiter
  
 - Example create: `https://twitchtokengenerator.com/api/create/QXV0aEZsb3dFeGFtcGxlIFRlc3QgQXBwbGljYXRpb24=/chat_login+user_read`
2. Response will be a json object including success bool, an id, and a message string containing the auth url. Present the URL to the program user.
3. Your application should ping the status endpoint for updates on authorization.  The status will return error 3 "Not authorized yet" until the user authorizes their account. After authorization, the status endpoint will return their credentials on the first ping post-authorization. Additional pings will return error 4 "API instance has already expired". This is to protect the user.
 - Status endpoint: `https://twitchtokengenerator.com/api/status`
 - Required Parameters:
 
 - - Id of auth flow

 - Example status: `https://twitchtokengenerator.com/api/status/rtotgzqct6ro6nwlwr04`
 - Please record your access token as well as the refresh token for usage.
4. Occasionally you will find that your Twitch access token has expired. This is new as of Twitch's oAuth2 implementation. To refresh, use the "refresh" token that you received in step 3 and hit the /api/refresh/ endpoint to get a new token.
 - Example refresh: `https://twitchtokengenerator.com/api/refresh/{refresh_token}`
### Credits
 - Xxplosions' twitchtv-oauth: [Xxplosions/twitchtv-oauth](https://github.com/Xxplosions/twitchtv-oauth)
 - MobileDetect: [http://mobiledetect.net/](http://mobiledetect.net/)
 
### License
MIT License. &copy; 2017 Cole
