<?php
/*
 * TwitchTV API code by Elias Ranz-Schleifer
 * Thank you for using my code please refer to
 * https://github.com/Xxplosions/twitchtv-oauth for future updates
 * Have questions or feedback? Contact Elias on Twitter (https://twitter.com/xxplosions)
 * Check out my livestream at http://twitch.tv/xxplosions (It would be amazing to chat with you about future updates)
 */
 
class TwitchTV {
  var $base_url = "";
  var $client_id = ''; //change this value, should be your TwitchTV Application Client ID
  var $client_secret = ""; //change this value, should be your TwitchTV Application Client Secret 
  var $redirect_url = ''; //change this value, should be your TwitchTV Application Rerdirect URL
  var $scope_array = array();
	
    /**
     * Channel data for the fetched user
     *
     * @var stdClass
     */
    var $channel_data = null;
    var $curl_cache;

    public function __construct() {
        $this->curl_cache = new TwitchTV_Curl_Cache();
    }

    /**
     * Generates a link based on the desired scope
     *
     * @return string         URL that is used to gain permissions for TwitchTV Authentication
     */
    public function authenticate() {
        $i      = 0;
        $return = '';
        $len    = count($this->scope_array);
        //search through the scope array and append a + foreach all but the last element
        foreach ($this->scope_array as $scope) {
            if ($i == $len - 1) {
                $scope .= "";
                $return .= $scope;
            } else {
                $scope .= "+";
                $return .= $scope;
            }

            $i++;
        }
        //initiate connection to the twitch.tv servers
        $scope            = $return;
        $authenticate_url = $this->base_url . 'oauth2/authorize?response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_url . '&scope=' . $scope;
        return $authenticate_url;
    }

    /**
     * Get's the access token for a specific user based on the code passed back from Twitch after Authenticating the application.
     *
     * @param string $code
     * @return string         Access token that is required by Twitch to make authenticated responses on behalf of the user
     */
    function get_access_token($code) {
        $ch = curl_init($this->base_url . "oauth2/token");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_url,
            'code' => $code
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data     = curl_exec($ch);
        $response = json_decode($data, true);
        return $response["access_token"];
    }

    /**
     * Gets the authenticated user based on an access token.
     * It's best to store this value in the database for future use.
     *
     * @param string $access_token
     * @return string         Username that is
     */
    function authenticated_user($access_token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: OAuth ' . $access_token
        ));
        $output   = curl_exec($ch);
        $response = json_decode($output, true);
        curl_close($ch);

        if (isset($response['token']['error'])) {
            return 'Unauthorized';
        } else {
            $username = $response['token']['user_name'];
            return $username;
        }
    }
    /**
     * Makes sure that the stream that is passed in is an actual channel on TwitchTV.
     *
     * @param string $username
     * @return boolean         TRUE means that the channel is valid
     *                        FALSE means that the channel is invalid
     */

    public function validate_stream($username) {
        $userid = $this->get_userid($username);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'users/' . $userid . '?client_id=' . $this->client_id . '&api_version=5'
        ));

        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            $decoded = json_decode($result);
            if (isset($decoded->error)) {
                return false;
            } else {
                return true;
            }
            print_r($decoded);
        }
    }

    /**
     * Loads a channel and its data
     *
     * @param string $channel
     * @return array         Array of data that includes the display name, Status, Chat links, game that the stream is playing and the banner
     */
    public function load_channel($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'channels/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return         = json_decode($result);
                $stream_details = array(
                    'display_name' => $return->display_name,
                    'status' => $return->status,
                    'chat' => $return->_links->chat,
                    'game' => $return->game,
                    'banner' => $return->banner
                );
                return $stream_details;
            }
        }
        curl_close($curl);
    }

    /**
     * Loads a username and its data
     *
     * @param string $username
     * @return array         Array of data that includes the display name, Status, Chat links, game that the stream is playing and the banner
     */
    public function get_userid($username) {
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'users/?login=' . $username . '&client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($username)) {
                $return         = json_decode($result, true);
                $user_id = $return['users'][0]['_id'];
                return $user_id;
            }
        }
        curl_close($curl);
    }

    /**
     * Loads the offline image for a given broadcaster display this if the channel is offline.
     *
     * @param string $channel
     * @return string         URL of the image that is given back by the Twitch. Uses a protocoless url on the front end
     */
    public function load_channel_offline_img($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'channels/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!curl_exec($curl)) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);

                $offline_img = $return->video_banner;


                return str_replace("http:", "", $offline_img);
            }
        }
        curl_close($curl);

    }

    /**
     * Grabs the video image that appears on the watch page if a stream is live.
     *
     * @param string $channel
     * @return string         URL of the image that is given back by the Twitch. Uses a protocoless url on the front end
     */
    public function load_channel_video_img($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!curl_exec($curl)) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);


                $preview = $return->stream->preview->medium;

                return str_replace("http:", "", $preview);

            }
        }
        curl_close($curl);

    }

    /**
     * Grabs the data for a given broadcast
     *
     * @param string $channel
     * @return array
     */
    public function get_broadcast_data($username) {
        return !empty($this->channel_data) ? $this->channel_data : $this->retrieve_channel_data($username);
    }

    /**
     * Grabs the stream title for a given stream
     *
     * @param string $username
     * @return string
     */
    public function get_stream_title($username) {
        return $this->get_broadcast_data($username)->status;
    }

    public function update_stream_title($access_token, $title = null, $game = null) {
        $username = $this->authenticated_user($access_token);
        $userid = $this->get_userid($username);
        if ($username != 'Unauthorized') {
            //get channel data so that you can make sure a value is being passed in and not setting it as an empty request
            $channel_data = json_decode(file_get_contents($this->base_url . 'channels/' . $userid));
            //no game? set it to the value that is stored in the API
            if ($game == null) {
                $game = $channel_data->game;
            }
            //no title? set it to the value in the API
            if ($title == null) {
                $title = $channel_data->status;
            }

            // make the API call and update stream information
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->base_url . "channels/" . $userid);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POST, 1);
            $fields = array(
                'channel[status]' => $title,
                'channel[game]' => $game
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: OAuth ' . $access_token
            ));
            $output   = curl_exec($ch);
            $response = json_decode($output, true);
            curl_close($ch);

            return true;
        }
    }

    /**
     * Grabs the viewer count of a given stream
     *
     * @param string $channel
     * @return array
     */
    public function load_stream_stats($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $result = $this->curl_cache->get_data('streams');
        if (!$result) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
            ));
            $result = curl_exec($curl);
        }

        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);

                // Cache data is only useful if we actually got something back
                $this->curl_cache->set_data('streams', $result);

                if ($return->stream == null) {
                    return;
                } else {
                    //echo "<pre>".print_r($return,true)."</pre>";
                    $stream_details = array(
                        'viewers' => $return->stream->viewers
                    );
                    return $stream_details;
                }
            }
        }
        curl_close($curl);
    }

    /**
     * Determins whether a stream is online or offline
     *
     * @param string $channel
     * @return string
     */
    public function stream_status($channel) {
        if ($channel) {
            $channelid = $this->get_userid($channel);
            //initiate connection to the twitch.tv servers
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
            ));
            $result = curl_exec($curl);
            //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
            if (!$result) {
                die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            } else {
                //cURL Response worked
                if (!empty($channel)) {
                    $return = json_decode($result);
                    if ($return->stream == null) {
                        $offline = "Stream Offline";
                        return $offline;
                    } else {
                        $online = "Stream Online";
                        return $online;
                    }
                }
            }
            curl_close($curl);
        } else {
            return;
        }
    }

    /**
     * Grabs the total number of followers that a stream currently has
     *
     * @param string $channel
     * @return int
     */
    public function follower_count($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'channels/' . $channelid . '/follows?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return    = json_decode($result);
                $followers = $return->_total;
                return $followers;
            }
        }
        curl_close($curl);
    }

    /**
     * Determines if a stream is online or not
     *
     * @param string $channel
     * @return boolean
     */
    public function stream_online_status($channel) {
        //initiate connection to the twitch.tv servers
        $channelid = $this->get_userid($channel);
        $result = $this->curl_cache->get_data('streams');
        if (!$result) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
            ));
            $result = curl_exec($curl);
        }

        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);

                $this->curl_cache->set_data('streams', $result);

                if ($return->stream == null) {
                    return false;
                } else {
                    return true;
                }
            }
        }
        curl_close($curl);
    }

    /**
     * Loads the video embed for twitch. Includes both object embed code (provided by twitch) and iframe for frameworks that don't support object players.
     *
     * @param string $channel
     * @param int $height
     * @param int $width
     * @return html
     */
    public function load_video($channel, $height = null, $width = null) {
        //defaults for stream embed dimensions, set so you can pass in the height and width outside of this function
        if ($height == null && $width == null) {
            $width  = 640;
            $height = 360;
        }
        //make sure that a channel is passed in so that it doesn't return an invalid embed code
        if (!empty($channel)) {
            //embed code for the video thanks to twitch.tv
            $embed_code = '<iframe width="' . $width . '" height="' . $height . '" src="http://player.twitch.tv/?channel=' . $channel . '&auto_play=true&start_volume=25" frameborder="0" allowfullscreen="true" auto_play="true" start_volume="25"></iframe>';
            return $embed_code;
        } else {
            return;
        }
    }

    /**
     * Loads the chat for a given channel embed code provided by Twitch
     *
     * @param string $channel
     * @param int $height
     * @param int $width
     * @return html
     */
    public function load_chat($channel, $height = null, $width = null) {
        //defaults for stream embed dimensions, set so you can pass in the height and width outside of this function
        if ($height == null && $width == null) {
            $width  = 350;
            $height = 500;
        }<?php
/*
 * TwitchTV API code by Elias Ranz-Schleifer
 * Thank you for using my code please refer to
 * https://github.com/Xxplosions/twitchtv-oauth for future updates
 * Have questions or feedback? Contact Elias on Twitter (https://twitter.com/xxplosions)
 * Check out my livestream at http://twitch.tv/xxplosions (It would be amazing to chat with you about future updates)
 */
 
class TwitchTV {
  var $base_url = "https://api.twitch.tv/kraken/";
  var $client_id = 'INSERT CLIENT ID HERE'; //change this value, should be your TwitchTV Application Client ID
  var $client_secret = "INSERT CLIENT SECRET HERE"; //change this value, should be your TwitchTV Application Client Secret 
  var $redirect_url = 'INSERT REDIRECT URL HERE'; //change this value, should be your TwitchTV Application Rerdirect URL
  var $scope_array = array('user_read','channel_read','chat_login','user_follows_edit','channel_editor','channel_commercial','channel_check_subscription');
	
    /**
     * Channel data for the fetched user
     *
     * @var stdClass
     */
    var $channel_data = null;
    var $curl_cache;

    public function __construct() {
        $this->curl_cache = new TwitchTV_Curl_Cache();
    }

    /**
     * Generates a link based on the desired scope
     *
     * @return string         URL that is used to gain permissions for TwitchTV Authentication
     */
    public function authenticate() {
        $i      = 0;
        $return = '';
        $len    = count($this->scope_array);
        //search through the scope array and append a + foreach all but the last element
        foreach ($this->scope_array as $scope) {
            if ($i == $len - 1) {
                $scope .= "";
                $return .= $scope;
            } else {
                $scope .= "+";
                $return .= $scope;
            }

            $i++;
        }
        //initiate connection to the twitch.tv servers
        $scope            = $return;
        $authenticate_url = $this->base_url . 'oauth2/authorize?response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_url . '&scope=' . $scope;
        return $authenticate_url;
    }

    /**
     * Get's the access token for a specific user based on the code passed back from Twitch after Authenticating the application.
     *
     * @param string $code
     * @return string         Access token that is required by Twitch to make authenticated responses on behalf of the user
     */
    function get_access_token($code) {
        $ch = curl_init($this->base_url . "oauth2/token");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_url,
            'code' => $code
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data     = curl_exec($ch);
        $response = json_decode($data, true);
        return $response["access_token"];
    }

    /**
     * Gets the authenticated user based on an access token.
     * It's best to store this value in the database for future use.
     *
     * @param string $access_token
     * @return string         Username that is
     */
    function authenticated_user($access_token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: OAuth ' . $access_token
        ));
        $output   = curl_exec($ch);
        $response = json_decode($output, true);
        curl_close($ch);

        if (isset($response['token']['error'])) {
            return 'Unauthorized';
        } else {
            $username = $response['token']['user_name'];
            return $username;
        }
    }
    /**
     * Makes sure that the stream that is passed in is an actual channel on TwitchTV.
     *
     * @param string $username
     * @return boolean         TRUE means that the channel is valid
     *                        FALSE means that the channel is invalid
     */

    public function validate_stream($username) {
        $userid = $this->get_userid($username);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'users/' . $userid . '?client_id=' . $this->client_id . '&api_version=5'
        ));

        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            $decoded = json_decode($result);
            if (isset($decoded->error)) {
                return false;
            } else {
                return true;
            }
            print_r($decoded);
        }
    }

    /**
     * Loads a channel and its data
     *
     * @param string $channel
     * @return array         Array of data that includes the display name, Status, Chat links, game that the stream is playing and the banner
     */
    public function load_channel($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'channels/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return         = json_decode($result);
                $stream_details = array(
                    'display_name' => $return->display_name,
                    'status' => $return->status,
                    'chat' => $return->_links->chat,
                    'game' => $return->game,
                    'banner' => $return->banner
                );
                return $stream_details;
            }
        }
        curl_close($curl);
    }

    /**
     * Loads a username and its data
     *
     * @param string $username
     * @return array         Array of data that includes the display name, Status, Chat links, game that the stream is playing and the banner
     */
    public function get_userid($username) {
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'users/?login=' . $username . '&client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($username)) {
                $return         = json_decode($result, true);
                $user_id = $return['users'][0]['_id'];
                return $user_id;
            }
        }
        curl_close($curl);
    }

    /**
     * Loads the offline image for a given broadcaster display this if the channel is offline.
     *
     * @param string $channel
     * @return string         URL of the image that is given back by the Twitch. Uses a protocoless url on the front end
     */
    public function load_channel_offline_img($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'channels/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!curl_exec($curl)) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);

                $offline_img = $return->video_banner;


                return str_replace("http:", "", $offline_img);
            }
        }
        curl_close($curl);

    }

    /**
     * Grabs the video image that appears on the watch page if a stream is live.
     *
     * @param string $channel
     * @return string         URL of the image that is given back by the Twitch. Uses a protocoless url on the front end
     */
    public function load_channel_video_img($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!curl_exec($curl)) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);


                $preview = $return->stream->preview->medium;

                return str_replace("http:", "", $preview);

            }
        }
        curl_close($curl);

    }

    /**
     * Grabs the data for a given broadcast
     *
     * @param string $channel
     * @return array
     */
    public function get_broadcast_data($username) {
        return !empty($this->channel_data) ? $this->channel_data : $this->retrieve_channel_data($username);
    }

    /**
     * Grabs the stream title for a given stream
     *
     * @param string $username
     * @return string
     */
    public function get_stream_title($username) {
        return $this->get_broadcast_data($username)->status;
    }

    public function update_stream_title($access_token, $title = null, $game = null) {
        $username = $this->authenticated_user($access_token);
        $userid = $this->get_userid($username);
        if ($username != 'Unauthorized') {
            //get channel data so that you can make sure a value is being passed in and not setting it as an empty request
            $channel_data = json_decode(file_get_contents($this->base_url . 'channels/' . $userid));
            //no game? set it to the value that is stored in the API
            if ($game == null) {
                $game = $channel_data->game;
            }
            //no title? set it to the value in the API
            if ($title == null) {
                $title = $channel_data->status;
            }

            // make the API call and update stream information
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->base_url . "channels/" . $userid);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POST, 1);
            $fields = array(
                'channel[status]' => $title,
                'channel[game]' => $game
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: OAuth ' . $access_token
            ));
            $output   = curl_exec($ch);
            $response = json_decode($output, true);
            curl_close($ch);

            return true;
        }
    }

    /**
     * Grabs the viewer count of a given stream
     *
     * @param string $channel
     * @return array
     */
    public function load_stream_stats($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $result = $this->curl_cache->get_data('streams');
        if (!$result) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
            ));
            $result = curl_exec($curl);
        }

        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);

                // Cache data is only useful if we actually got something back
                $this->curl_cache->set_data('streams', $result);

                if ($return->stream == null) {
                    return;
                } else {
                    //echo "<pre>".print_r($return,true)."</pre>";
                    $stream_details = array(
                        'viewers' => $return->stream->viewers
                    );
                    return $stream_details;
                }
            }
        }
        curl_close($curl);
    }

    /**
     * Determins whether a stream is online or offline
     *
     * @param string $channel
     * @return string
     */
    public function stream_status($channel) {
        if ($channel) {
            $channelid = $this->get_userid($channel);
            //initiate connection to the twitch.tv servers
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
            ));
            $result = curl_exec($curl);
            //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
            if (!$result) {
                die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            } else {
                //cURL Response worked
                if (!empty($channel)) {
                    $return = json_decode($result);
                    if ($return->stream == null) {
                        $offline = "Stream Offline";
                        return $offline;
                    } else {
                        $online = "Stream Online";
                        return $online;
                    }
                }
            }
            curl_close($curl);
        } else {
            return;
        }
    }

    /**
     * Grabs the total number of followers that a stream currently has
     *
     * @param string $channel
     * @return int
     */
    public function follower_count($channel) {
        $channelid = $this->get_userid($channel);
        //initiate connection to the twitch.tv servers
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->base_url . 'channels/' . $channelid . '/follows?client_id=' . $this->client_id . '&api_version=5'
        ));
        $result = curl_exec($curl);
        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return    = json_decode($result);
                $followers = $return->_total;
                return $followers;
            }
        }
        curl_close($curl);
    }

    /**
     * Determines if a stream is online or not
     *
     * @param string $channel
     * @return boolean
     */
    public function stream_online_status($channel) {
        //initiate connection to the twitch.tv servers
        $channelid = $this->get_userid($channel);
        $result = $this->curl_cache->get_data('streams');
        if (!$result) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->base_url . 'streams/' . $channelid . '?client_id=' . $this->client_id . '&api_version=5'
            ));
            $result = curl_exec($curl);
        }

        //makes sure that the cURL was excuted if not it generates the error stating that it didn't succeed.
        if (!$result) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        } else {
            //cURL Response worked
            if (!empty($channel)) {
                $return = json_decode($result);

                $this->curl_cache->set_data('streams', $result);

                if ($return->stream == null) {
                    return false;
                } else {
                    return true;
                }
            }
        }
        curl_close($curl);
    }

    /**
     * Loads the video embed for twitch. Includes both object embed code (provided by twitch) and iframe for frameworks that don't support object players.
     *
     * @param string $channel
     * @param int $height
     * @param int $width
     * @return html
     */
    public function load_video($channel, $height = null, $width = null) {
        //defaults for stream embed dimensions, set so you can pass in the height and width outside of this function
        if ($height == null && $width == null) {
            $width  = 640;
            $height = 360;
        }
        //make sure that a channel is passed in so that it doesn't return an invalid embed code
        if (!empty($channel)) {
            //embed code for the video thanks to twitch.tv
            $embed_code = '<iframe width="' . $width . '" height="' . $height . '" src="http://player.twitch.tv/?channel=' . $channel . '&auto_play=true&start_volume=25" frameborder="0" allowfullscreen="true" auto_play="true" start_volume="25"></iframe>';
            return $embed_code;
        } else {
            return;
        }
    }

    /**
     * Loads the chat for a given channel embed code provided by Twitch
     *
     * @param string $channel
     * @param int $height
     * @param int $width
     * @return html
     */
    public function load_chat($channel, $height = null, $width = null) {
        //defaults for stream embed dimensions, set so you can pass in the height and width outside of this function
        if ($height == null && $width == null) {
            $width  = 350;
            $height = 500;
        }
        //make sure that a channel is passed in so that it doesn't return an invalid embed code
        if (!empty($channel)) {
            //embed code thanks to twitch.tv
            $embed_code = '<iframe frameborder="0" scrolling="no" id="chat_embed" src="http://twitch.tv/chat/embed?channel=' . $channel . '&amp;popout_chat=true" height="' . $height . '" width="100%"></iframe>';
            return $embed_code;
        } else {
            return;
        }
    }

    /**
     * Gets a complete list of games that are currrently live on Twitch for use when updating stream title can be used to populate an autocomplete.
     *
     * @return array
     */
    public function get_games() {
        $game = array();
        for ($i = 0; $i < 5; $i++) {
            $offset = 100 * $i;
            $obj    = json_decode(file_get_contents('https://api.twitch.tv/kraken/games/top?limit=100&offset=' . $offset));
            if ($obj) {
                foreach ($obj->top as $top) {
                    $game[] = $top;
                }
            }
        }
        $games = array();
        foreach ($game as $game) {
            $games[] = $game->game->name;
        }
        return json_encode($games);
    }

    /**
     * Sends a request to twitch to run a commercial on a given channel
     *
     * @param string $access_token
     * @param int $length
     * @return boolean
     */
    public function run_commercial($access_token, $length = 30) {
        $username = $this->authenticated_user($access_token);
        $userid = $this->get_userid($username);
        $ch       = curl_init($this->base_url . "channels/" . $userid . '/commercial');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Authorization: OAuth ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'client_id' => $this->client_id,
            'length' => $length
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data     = curl_exec($ch);
        $response = json_decode($data, true);
        return true;
    }

    /**
     * Make a request to follow a given channel
     *
     * @param string $channel
     * @param string $access_token
     * @return boolean
     */
    public function follow_channel($channel, $access_token) {
        $username = $this->authenticated_user($access_token);
        $userid = $this->get_userid($username);
        $channelid = $this->get_userid($channel);
        $ch       = curl_init($this->base_url . "users/" . $userid . "/follows/channels/" . $channelid);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Authorization: OAuth ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'client_id' => $this->client_id
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data = curl_exec($ch);
        return true;
    }

    /**
     * Retrieve the data for a specific channel used to make cached calls throughout the class
     *
     * @param string $username
     * @return array
     */
    private function retrieve_channel_data($username) {
        $userid = $this->get_userid($username);
        return json_decode(file_get_contents($this->base_url . 'channels/' . $userid));
    }

}


/**
 * A class to ensure that there aren't more calls to the API than needed
 * Written by Robarelli
 */
class TwitchTV_Curl_Cache {

    /**
     * Holds the cache data
     */
    private $cache = array();

    /**
     * Retrieve the cache data
     *
     * @param string $id the cache key
     * @return multitype:
     */
    public function get_data($id) {
        return array_key_exists($id, $this->cache) ? $this->cache[$id] : null;
    }

    /**
     * Set the cache data
     *
     * @param string $id the cache key. Recommended to be the slug name of the url in the cURL request
     * @param unknown $data
     */
    public function set_data($id, $data) {
        return $this->cache[$id] = $data;
    }

    /**
     * Remove an item from the cache
     *
     * @param string $id
     */
    public function unset_data($id) {
        unset($this->cache[$id]);
    }

    /**
     * Checks if a key has been set in the cache
     *
     * @param string $id
     * @return boolean
     */
    public function data_exists($id) {
        return !empty($this->cache[$id]);
    }
}
?>
        //make sure that a channel is passed in so that it doesn't return an invalid embed code
        if (!empty($channel)) {
            //embed code thanks to twitch.tv
            $embed_code = '<iframe frameborder="0" scrolling="no" id="chat_embed" src="http://twitch.tv/chat/embed?channel=' . $channel . '&amp;popout_chat=true" height="' . $height . '" width="100%"></iframe>';
            return $embed_code;
        } else {
            return;
        }
    }

    /**
     * Gets a complete list of games that are currrently live on Twitch for use when updating stream title can be used to populate an autocomplete.
     *
     * @return array
     */
    public function get_games() {
        $game = array();
        for ($i = 0; $i < 5; $i++) {
            $offset = 100 * $i;
            $obj    = json_decode(file_get_contents('https://api.twitch.tv/kraken/games/top?limit=100&offset=' . $offset));
            if ($obj) {
                foreach ($obj->top as $top) {
                    $game[] = $top;
                }
            }
        }
        $games = array();
        foreach ($game as $game) {
            $games[] = $game->game->name;
        }
        return json_encode($games);
    }

    /**
     * Sends a request to twitch to run a commercial on a given channel
     *
     * @param string $access_token
     * @param int $length
     * @return boolean
     */
    public function run_commercial($access_token, $length = 30) {
        $username = $this->authenticated_user($access_token);
        $userid = $this->get_userid($username);
        $ch       = curl_init($this->base_url . "channels/" . $userid . '/commercial');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Authorization: OAuth ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'client_id' => $this->client_id,
            'length' => $length
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data     = curl_exec($ch);
        $response = json_decode($data, true);
        return true;
    }

    /**
     * Make a request to follow a given channel
     *
     * @param string $channel
     * @param string $access_token
     * @return boolean
     */
    public function follow_channel($channel, $access_token) {
        $username = $this->authenticated_user($access_token);
        $userid = $this->get_userid($username);
        $channelid = $this->get_userid($channel);
        $ch       = curl_init($this->base_url . "users/" . $userid . "/follows/channels/" . $channelid);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Authorization: OAuth ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'client_id' => $this->client_id
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data = curl_exec($ch);
        return true;
    }

    /**
     * Retrieve the data for a specific channel used to make cached calls throughout the class
     *
     * @param string $username
     * @return array
     */
    private function retrieve_channel_data($username) {
        $userid = $this->get_userid($username);
        return json_decode(file_get_contents($this->base_url . 'channels/' . $userid));
    }

}


/**
 * A class to ensure that there aren't more calls to the API than needed
 * Written by Robarelli
 */
class TwitchTV_Curl_Cache {

    /**
     * Holds the cache data
     */
    private $cache = array();

    /**
     * Retrieve the cache data
     *
     * @param string $id the cache key
     * @return multitype:
     */
    public function get_data($id) {
        return array_key_exists($id, $this->cache) ? $this->cache[$id] : null;
    }

    /**
     * Set the cache data
     *
     * @param string $id the cache key. Recommended to be the slug name of the url in the cURL request
     * @param unknown $data
     */
    public function set_data($id, $data) {
        return $this->cache[$id] = $data;
    }

    /**
     * Remove an item from the cache
     *
     * @param string $id
     */
    public function unset_data($id) {
        unset($this->cache[$id]);
    }

    /**
     * Checks if a key has been set in the cache
     *
     * @param string $id
     * @return boolean
     */
    public function data_exists($id) {
        return !empty($this->cache[$id]);
    }
}
?>