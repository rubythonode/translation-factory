<?php

namespace ChrisKonnertz\TranslationFactory\Controllers;

use ChrisKonnertz\TranslationFactory\IO\TranslationReaderInterface;
use ChrisKonnertz\TranslationFactory\TranslationFactory;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;

class TranslationFileController extends AuthController
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * TranslationFactoryController constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        if ($config->get(TranslationFactory::CONFIG_NAME.'.user_authentication')) {
            //$this->middleware('auth');
        }

        $this->config = $config;
    }

    /**
     * Index page of the package
     *
     * @param string $hash $config
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(string $hash)
    {
        $this->ensureAuth();

        /** @var TranslationFactory $translationFactory */
        $translationFactory = app()->get('translation-factory');

        $translationReader = $translationFactory->getTranslationReader();
        $translationBag = $this->getBagByHash($translationReader, $hash);

        $currentItemKey = null;
        $autoTranslation = null;
        $targetLanguage = $translationFactory->getTargetLanguage();
        $data = compact('translationBag', 'currentItemKey', 'targetLanguage', 'autoTranslation');
        return view('translationFactory::file', $data);
    }

    /**
     * Shows the translation file page with a text area for editing a translation item
     *
     * @param string $hash
     * @param string $currentItemKey
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(string $hash, string $currentItemKey)
    {
        $this->ensureAuth();

        /** @var TranslationFactory $translationFactory */
        $translationFactory = app()->get('translation-factory');

        $translationReader = $translationFactory->getTranslationReader();
        $translationBag = $this->getBagByHash($translationReader, $hash);

        $baseLanguage = $this->config->get('app.locale');
        $targetLanguage = $translationFactory->getTargetLanguage();

        $autoTranslation = null;
        if (! $translationBag->hasTranslation($targetLanguage, $currentItemKey)) {
            if ($translationFactory->canTranslate(strtoupper($baseLanguage), strtoupper($targetLanguage))) {
                try {
                    $autoTranslation = $translationFactory->translate(
                        $translationBag->getTranslation($baseLanguage, $currentItemKey)
                    );
                } catch (\Exception $exception) {
                    // do nothing
                }
            }
        }

        $data = compact('translationBag', 'currentItemKey', 'baseLanguage', 'targetLanguage', 'autoTranslation');
        return view('translationFactory::file', $data);
    }

    /**
     * Updates a translation item
     *
     * @param Request $request
     * @param string  $hash
     * @param string  $currentItemKey
     */
    public function update(Request $request, string $hash, string $currentItemKey)
    {
        $this->ensureAuth();

        $translation = $request->input('translation');

        // The translation value can be sent but be null, which is not a valid value,
        // so change it to an empty string instead
        if ($translation === null) {
            $translation = '';
        }

        /** @var TranslationFactory $translationFactory */
        $translationFactory = app()->get('translation-factory');

        $translationReader = $translationFactory->getTranslationReader();
        $translationBag = $this->getBagByHash($translationReader, $hash);

        $translationBag->setTranslation($translationFactory->getTargetLanguage(), $currentItemKey, $translation);

        $translationWriter = $translationFactory->getTranslationWriter();
        $translationWriter->write($translationBag);
    }

    /**
     * Returns a translation bag that is identified by its hash
     *
     * @param TranslationReaderInterface $translationReader
     * @param string                     $hash
     * @return \ChrisKonnertz\TranslationFactory\TranslationBag
     * @throws \Exception
     */
    public function getBagByHash(TranslationReaderInterface $translationReader, string $hash)
    {
        $this->ensureAuth();

        $translationBags = $translationReader->readAll();

        $currentBag = null;
        foreach ($translationBags as $translationBag) {
            if ($translationBag->getHash() === $hash) {
                $currentBag = $translationBag;
                break;
            }
        }

        if ($currentBag === null) {
            throw new \Exception('Could not find a translation bag with this hash: '.$hash);
        }

        return $currentBag;
    }

}
