<?php
    class VideoSearch extends EngineRequest {
        protected $instance_url;
        public function get_request_url() {
            $query = urlencode($this->query);
            $key = "AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8";
            return "https://www.youtube.com/youtubei/v1/search?key=$key";
        }

        public function parse_results($response) {
            $results = array();
            $xpath = get_xpath($response);
            error_log($response);

            if (!$xpath)
                return $results;

            $results = array();

            foreach($xpath->query("//ytd-video-renderer") as $result) {
                $url = $xpath->evaluate(".//div//div//div//div//h3//a[@id='results']//@href", $result)[0];

                if ($url == null)
                    continue;

                $url = $url->textContent;


                if (!empty($results) && array_key_exists("url", $results) && end($results)["url"] == $url->textContent)
                    continue;

                $title = $xpath->evaluate(".//div//div//div//div//h3//a[@id='results']", $result)[0];

                if ($title == null)
                    continue;

                $title = $title->textContent;


                $uploader = $xpath->evaluate(".//div//div//div[@id='channel_info']//ytd-channel-name[@id='channel-name']", $result)[0]->textContent;

                $metadata = $xpath->evaluate(".//div//div//div//ytd-video-meta-block//div[@id='metadata']//div[@id='metadata-line]//span", $result);

                $views = $metadata[0];
                $date = $metadata[1];

                array_push($results,
                    array (
                        "title" => htmlspecialchars($title),
                        "url" =>  htmlspecialchars($url),
                        // base_url is to be removed in the future, see #47
                        "base_url" => htmlspecialchars(get_base_url($url)),
                        "uploader" => htmlspecialchars($uploader),
                        "views" => htmlspecialchars($views),
                        "date" => htmlspecialchars($date),
                        "thumbnail" => htmlspecialchars($thumbnail)
                    )
                );
            }

            return $results;
        }

        public static function print_results($results, $opts) {
            echo "<div class=\"text-result-container\">";

                foreach($results as $result) {
                    $title = $result["title"];
                    $url = $result["url"];
                    $url = check_for_privacy_frontend($url, $opts);
                    $base_url = get_base_url($url);
                    $uploader = $result["uploader"];
                    $views = $result["views"];
                    $date = $result["date"];
                    $thumbnail = $result["thumbnail"];

                    echo "<div class=\"text-result-wrapper\">";
                    echo "<a href=\"$url\">";
                    echo "$base_url";
                    echo "<h2>$title</h2>";
                    echo "<img class=\"video-img\" src=\"image_proxy.php?url=$thumbnail\">";
                    echo "<br>";
                    echo "<span>$uploader - $date - $views views</span>";
                    echo "</a>";
                    echo "</div>";
                }

            echo "</div>";
        }
    }
?>
