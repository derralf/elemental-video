<?php

namespace Derralf\Elements\Video\Element;

use Bummzack\SortableFile\Forms\SortableUploadField;
use DNADesign\Elemental\Models\BaseElement;
use Embed\Embed;
use Sheadawson\Linkable\Forms\LinkField;
use Sheadawson\Linkable\Models\Link;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use UncleCheese\DisplayLogic\Forms\Wrapper;


class ElementVideo extends BaseElement
{

    public function getType()
    {
        return self::$singular_name;
    }

    private static $icon = 'font-icon-block-media';

    private static $table_name = 'ElementVideo';

    private static $singular_name = 'Video-Element';
    private static $plural_name = 'Video-Elemente';
    private static $description = '';

    private static $db = [
        'HTML'                  => 'HTMLText',
        'MediaAspectRatio'      => "Enum('16by9,4by3,1by1,9by16','16by9')",
        'MediaSourceURL'        => 'Varchar(255)',
        'MediaType'             => 'Varchar',
        'MediaWidth'            => 'Int',
        'MediaHeight'           => 'Int',
        'MediaEmbedHTML'        => 'Text',
        'MediaCredits'          => 'HTMLText',
        'MediaSourceType'       =>  "Enum('External,Internal','External')",
        'MediaExternalImageUrl' => 'Varchar(255)',
    ];

    private static $has_one = [
        'VideoFileMP4'      =>  File::class,
        'VideoFileWEBM'     =>  File::class,
        'VideoFileOGV'      =>  File::class,
        'ReadMoreLink' => Link::Class
    ];

    private static $defaults = [
        'MediaAspectRatio' => '16by9',
        'MediaWidth'       => '847',
        'MediaHeight'      => '635'
    ];

    private static $owns = [
        'VideoFileMP4',
        'VideoFileWEBM',
        'VideoFileOGV'
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {

            // Style: Description for default style (describes Layout thats used, when no special style is selected)
            $Style = $fields->dataFieldByName('Style');
            $StyleDefaultDescription = $this->owner->config()->get('styles_default_description', Config::UNINHERITED);
            if ($Style && $StyleDefaultDescription) {
                $Style->setDescription($StyleDefaultDescription);
            }

            // ReadMoreLink: use Linkfield
            $ReadMoreLink = LinkField::create('ReadMoreLinkID', 'Link');
            $fields->replaceField('ReadMoreLinkID', $ReadMoreLink);


            // Video Tab
            $fields->insertAfter(new Tab('VideoTab', _t(__CLASS__ . '.VideoTab','Video') ), 'Main');

            // Video Link Typ: intern/extern
            $MediaSourceType = new DropdownField('MediaSourceType','Video Link-Typ',singleton(__CLASS__)->dbObject('MediaSourceType')->enumValues());
            $MediaSourceType -> setDescription('Quelle: extern (YouTube, Vimeo) oder intern (self-hosted)');
            $fields -> addFieldTotab('Root.VideoTab', $MediaSourceType);

            // Video Typ (nur zur Kontrolle)
            $MediaType = new Textfield('MediaType','MediaType');
            $MediaType = $MediaType -> performReadonlyTransformation();
            $fields -> addFieldTotab('Root.VideoTab', $MediaType);

            // Video Format / Seitenverhältnis / Aspect MediaAspectRatio
            $MediaAspectRatio = new DropdownField('MediaAspectRatio','Video Seitenverhältnis',singleton(__CLASS__)->dbObject('MediaAspectRatio')->enumValues());
            $fields -> addFieldTotab('Root.VideoTab', $MediaAspectRatio);

            // Video internal / self-hosted Video: MP4
            $fields->removeByName('VideoFileMP4');
            $VideoFileMP4 = new UploadField('VideoFileMP4', 'Video (.mp4)');
            $VideoFileMP4->setFolderName('video');
            $VideoFileMP4->getValidator()-> setAllowedExtensions(array('mp4'));
            $VideoFileMP4->setDescription('Video im Format MP4 (h.264 wichtig).<br>Erlaubte Dateiformate: .mp4');
            $VideoFileMP4Wrapper = Wrapper::create($VideoFileMP4)->displayIf("MediaSourceType")->isEqualTo("Internal")->end();
            $fields->addFieldToTab('Root.VideoTab', $VideoFileMP4Wrapper);

            // Video internal / self-hosted Video: WEBM
            $fields->removeByName('VideoFileWEBM');
            $VideoFileWEBM = new UploadField('VideoFileWEBM', 'Video (.webm)');
            $VideoFileWEBM -> setFolderName('video');
            $VideoFileWEBM -> getValidator() -> setAllowedExtensions(array('webm'));
            $VideoFileWEBM -> setDescription('optional Video im Format WEBM (alternatives Format, u.a. von Chrome, Opera, Mozilla verwendet).<br>Erlaubte Dateiformate: .webm');
            $VideoFileWEBMWrapper = Wrapper::create($VideoFileWEBM)->displayIf("MediaSourceType")->isEqualTo("Internal")->end();
            $fields->addFieldToTab('Root.VideoTab', $VideoFileWEBMWrapper);

            // Video internal / self-hosted Video: OGV
            $fields->removeByName('VideoFileOGV');
            $VideoFileOGV = new UploadField('VideoFileOGV', 'Video (.ogg, .ogv)');
            $VideoFileOGV -> setFolderName('video');
            $VideoFileOGV -> getValidator() -> setAllowedExtensions(array('ogv','ogg'));
            $VideoFileOGV -> setDescription('optional Video im Format OGG Theora (optional als Fallback für ältere Firefox und Opera nötig).<br>Erlaubte Dateiformate: .ogv, .ogg');
            $VideoFileOGVWrapper = Wrapper::create($VideoFileOGV)->displayIf("MediaSourceType")->isEqualTo("Internal")->end();
            $fields->addFieldToTab('Root.VideoTab', $VideoFileOGVWrapper);

            // Video extern : URL/Quelle
            $VideoSourceURL = new Textfield('MediaSourceURL','MediaSourceURL');
            $VideoSourceURL->setDescription('URL zum Video, z.B.:<br>- https://www.youtube-nocookie.com/watch?v=sKERsNdoO8w oder <br>- https://vimeo.com/12739181');
            $VideoSourceURL->displayIf("MediaSourceType")->isEqualTo("External")->end();
            $fields -> addFieldTotab('Root.VideoTab', $VideoSourceURL);

            // Video extern: Infotext
            $FieldsInfo = new LiteralField('FieldsInfo','<p class="message warning">HINWEIS:<br>normalerweise sollte es aureichen das Feld oben(Titel, URL) auszufüllen.<br>Änderungen an den Feldern unterhalb auf eigene Gefahr<br>Diese Felder werden außerdem u.U. automatisch vom System überschrieben.</<p>');
            $FieldsInfoWrapper = Wrapper::create($FieldsInfo)->displayIf("MediaSourceType")->isEqualTo("External")->end();
            $fields -> addFieldTotab('Root.VideoTab', $FieldsInfoWrapper);

            // Video: Einbettungscode
            $MediaEmbedHTML = new TextAreafield('MediaEmbedHTML','Einbettungscode');
            $MediaEmbedHTML->setDescription('Der Code wird nach speichern automatisch aus dem URL, Breite und Höhe (bei lokal gehosteten Videos aus den hochgeladenen Video-Dateien) erzeugt<br>Falls das nicht funktioniert, kann nötigenfalls auch der URL leer gelassen und der Embed-Code hier von Hand eingetragen werden, z.B.:<br><code>'.htmlspecialchars ('<iframe width="847" height="476" src="https://www.youtube.com/embed/sKERsNdoO8w?feature=oembed" frameborder="0" allowfullscreen></iframe>').'</code>'
                .'<br>Seitenverhältnisse quer: 56.25 = 16:9 / 75 = 4:3 / 66 = 3:2 / 80 = 4:5 / 100 = 1:1'
                .'<br>Seitenverhältnisse hoch:178 = 9:16');
            $fields -> addFieldTotab('Root.VideoTab', $MediaEmbedHTML);

            // Video: Vorschaubild bei externem Video
            $MediaExternalImageUrl = new Textfield('MediaExternalImageUrl','URL externes Vorschaubild');
            $MediaExternalImageUrl->setDescription('bei externen Video (z.B. Youtube) versucht das System das passende/richtige Vorschaubild zu ermitteln<br>wird hier nicht verwendet, könnte aber theoretisch z.B. in einer Übersicht als Teaser-Image verwendet werden');
            $fields -> addFieldTotab('Root.VideoTab', $MediaExternalImageUrl);

            // Video: Credits
            $MediaCredits = new HtmlEditorField('MediaCredits','Video Credits');
            $MediaCredits -> setDescription('optional: weiterer Text, unter dem Video angezeigt');
            $fields -> addFieldTotab('Root.VideoTab', $MediaCredits);



        });
        $fields = parent::getCMSFields();
        return $fields;
    }


    public function ReadmoreLinkClass() {
        return $this->config()->get('readmore_link_class');
    }



    public function onBeforeWrite() {
        // $this->updateEmbedHTML();

        $changes = $this->getChangedFields();


        if (isset($changes['MediaWidth']) && $changes['MediaWidth']['before']) {
            $this->updateEmbedHTML();
        }
        if (isset($changes['MediaHeight']) && $changes['MediaHeight']['before']) {
            $this->updateEmbedHTML();
        }
        if (isset($changes['MediaSourceURL']) && $changes['MediaSourceURL']['before']) {
            $this->updateEmbedHTML();
        }
        if (!$this->MediaEmbedHTML) {
            $this->updateEmbedHTML();
        }
        if (!$this->owner->MediaType) {
            $this->updateEmbedHTML();
        }
        if (isset($changes['VideoFileMP4ID']) || isset($changes['VideoFileWEBMID']) || isset($changes['VideoFileOGVID'])) {
            $this->updateEmbedHTML();
        }
        if (isset($changes['VideoSourceType'])) {
            $this->updateEmbedHTML();
        }

        parent::onBeforeWrite();
    }


    public function updateEmbedHTML() {
        $this->MediaExternalImageUrl = '';
        if ($this->MediaSourceType == "Internal") {
            $this->setFromInternalFile();
        } else {
            $this->setFromURL($this->MediaSourceURL);
        }
    }

    public function setFromInternalFile() {
        $this->MediaType = 'video';
        $MediaType = 'video';
        $EmbedHTML = '';
        if($this->VideoFileMP4()->exists()) {
            $MediaType = 'video';
            $EmbedHTML .= '<video controls preload="automatic">';
            $EmbedHTML .= '<source src="' . $this->VideoFileMP4()->Link() . '" type=\'video/mp4; codecs="avc1.42E01E, mp4a.40.2"\'>';
            if ($this->VideoFileWEBM()->exists()) $EmbedHTML .= '<source src="' . $this->owner->VideoFileWEBM()->Link() . '" type=\'video/webm; codecs="vp8, vorbis"\'>';
            if ($this->VideoFileOGV()->exists()) $EmbedHTML .= '<source src="' . $this->owner->VideoFileOGV()->Link() . '" type=\'video/ogg; codecs="theora, vorbis"\'>';
            $EmbedHTML .= '</video>';

            $EmbedHTML .= "\n Width " . $this->VideoFileMP4->getWidth();
        }
        $this->MediaType = $MediaType;
        $this->MediaoEmbedHTML = $EmbedHTML;
    }

    public function setFromURL($url) {
        $options = array();
        $options['min_image_width'] = 770;
        $options['choose_bigger_image'] = true;

        if($url){
            $info = Embed::create($this->MediaSourceURL, $options = $options);
            $this->setFromEmbed($info);
        }
    }


    public function setFromEmbed($info) {

        $MediaType = $info->type;
        $EmbedHTML = $info->code;
        $ExternalImages = $info->images;
        $ExternalImageUrl = "";

        // Vorschaubild für externes Video
        if($ExternalImages && is_array($ExternalImages) && !empty($ExternalImages)) {
            $image = $ExternalImages[0];
            if (stristr ( $image["mime"] , "jpeg")) {
                $ExternalImageUrl = $image["url"];
            }
        }


        // keine "related videos" am ende (youtube)
        if(strpos($EmbedHTML, 'youtu.be') !== false || strpos($EmbedHTML, 'youtube.com') !== false){
            // add "&rel=0"
            $EmbedHTML = preg_replace("@src=(['\"])?([^'\">\s]*)@", "src=$1$2&amp;rel=0", $EmbedHTML);
            // remove frameborder="0"
            $EmbedHTML = preg_replace("@frameborder=\"0\"@", "", $EmbedHTML);
        }

        // Datenschutzkonform einsetzen von youtube-nocookie.com
        $EmbedHTML = str_replace ( 'www.youtube.com' , 'www.youtube-nocookie.com', $EmbedHTML);

        // Seitenverhältnis als Kommentar anhängen zum spicken
        if($info->aspectRatio) $EmbedHTML .= "\n <!-- aspectRatio: {$info->aspectRatio} -->";

        $this->MediaType = $MediaType;
        $this->MediaEmbedHTML = $EmbedHTML;
        $this->MediaExternalImageUrl = $ExternalImageUrl;
    }


    public function getEmbedCode() {

        if(!$this->MediaEmbedHTML) return false;

        switch($this->MediaType) {
            case 'video':
            case 'rich':
                return '<div class="embed-video embed-responsive embed-responsive-'.$this->MediaAspectRatio.'">'.$this->MediaEmbedHTML.'</div>';
                break;
        }
    }

















}