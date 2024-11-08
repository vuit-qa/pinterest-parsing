<?php

        function isPinterestVideo($sourse) {
        	if(strpos($sourse, 'video-snippet') !== false) {
        	    return true;
        	}
        	return false;
        }
        
        function getPinterestPage($url) {
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
        	$curl = curl_init($url);
        	curl_setopt($curl, CURLOPT_URL, $url);
        	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        	$response = curl_exec($curl);
        	curl_close($curl);
        	return $response;
        }
        
        function getPinterestPinName($sourse) {
        	$pattern = '/<h1[^>]*>([^<]+)<\/h1>/';
            if (preg_match($pattern, $sourse, $matches)) {
                return $matches[1];
            } else {
                return 'Name is missing';
            }
        }
        
        function getPinterestPinDescription($sourse) {
        	$pattern = '/<span class="richPinInformation"[^>]*>.*?<span>(.*?)<\/span>/s';
            if (preg_match($pattern, $sourse, $matches)) {
                return trim($matches[1]);
            } else {
                return 'Description is missing';
            }
        }     
        
        
        

        
        function getPinterestMedia($sourse) {
            if (isPinterestVideo($sourse)) {
                $pattern = '/<script data-test-id="video-snippet".+?<\/script>/';
                preg_match($pattern, $sourse, $matches);
                $json = json_decode(str_replace(array('<script data-test-id="video-snippet" type="application/ld+json">', '</script>'), '', $matches[0]), true);
                if($json["contentUrl"] == '') {
                    return [
                        "type" => "VideoObject",
                        "name" => $json["name"],
                        "description" => $json["description"],
                        "uploadDate" => $json["uploadDate"],
                        "creator" => [
                            "name" => $json["creator"]["name"],
                            "url" => $json["creator"]["url"],
                            "alternateName" => $json["creator"]["alternateName"],
                            "name" => $json["creator"]["name"],
                            "subsCount" => $json["creator"]["interactionStatistic"][0]["InteractionCount"]
                        ],
                        "keywords" => $json["keywords"],
                        "no_link" => true
                    ];
                } else {
                    return [
                        "type" => "VideoObject",
                        "name" => $json["name"],
                        "description" => $json["description"],
                        "uploadDate" => $json["uploadDate"],
                        "creator" => [
                            "name" => $json["creator"]["name"],
                            "url" => $json["creator"]["url"],
                            "alternateName" => $json["creator"]["alternateName"],
                            "name" => $json["creator"]["name"],
                            "subsCount" => $json["creator"]["interactionStatistic"][0]["InteractionCount"]
                        ],
                        "keywords" => $json["keywords"],
                        "link" => $json["contentUrl"]
                    ];
                }
            } else {
                $pattern = '/<script data-test-id="leaf-snippet".+?<\/script>/';
                preg_match($pattern, $sourse, $matches);
                $json = json_decode(str_replace(array('<script data-test-id="leaf-snippet" type="application/ld+json">', '</script>'), '', $matches[0]), true);
                if($json["image"] == '') {
                    return [
                        "type" => "SocialMediaPosting",
                        "author" => [
                            "name" => $json["author"]["name"],
                            "url" => $json["author"]["url"],
                        ],
                        "headline" => $json["headline"],
                        "articleBody" => $json["articleBody"],
                        "datePublished" => $json["datePublished"],
                        "no_link" => true
                    ];
                } else {
                    return [
                        "type" => "SocialMediaPosting",
                        "author" => [
                            "name" => $json["author"]["name"],
                            "url" => $json["author"]["url"],
                        ],
                        "headline" => $json["headline"],
                        "articleBody" => $json["articleBody"],
                        "datePublished" => $json["datePublished"],
                        "link" => $json["image"]
                    ];
                }
                
            }
        }
        function parseTimePin($isoDate) {
            $dateTime = new DateTime($isoDate);
            $dateTime->setTimezone(new DateTimeZone('Europe/Moscow'));
            $formattedDate = $dateTime->format('Y-m-d H:i:s');
            return $formattedDate;
        }
        function formatSubscribers($number, $lang = 'en') {
            $__gdd_m = (($lang == 'ru') ? " млн" : "M");
            $__gdd_k = (($lang == 'ru') ? " тыс." : "k");
            if ($number >= 1000000) {
                $formattedNumber = number_format($number / 1000000, 1, ',', ' ') . $__gdd_m;
            } elseif ($number >= 1000) {
                $formattedNumber = number_format($number / 1000, 1, ',', ' ') . $__gdd_k;
            } else {
                $formattedNumber = $number;
            }
        
            return $formattedNumber . ' ' . getSubscriberDeclension($number, $lang);
        }
        
        function getSubscriberDeclension($number1, $lang = 'en') {
            $number = abs($number1) % 100; // Получаем последние две цифры
            $last_digit = $number % 10;
            if($number1 == 1) {
                return (($lang == 'ru') ? "подписчик" : "subscriber");
            } else {
                if ($number >= 11 && $number <= 19) {
                    return (($lang == 'ru') ? "подписчиков" : "subscribers");
                } elseif ($last_digit >= 2 && $last_digit <= 4) {
                    return (($lang == 'ru') ? "подписчика" : "subscribers");
                } elseif ($last_digit == 1) {
                    return (($lang == 'ru') ? "подписчик" : "subscribers");
                }
            }
            return (($lang == 'ru') ? "подписчиков" : "subscribers");
        }
        
        
?>