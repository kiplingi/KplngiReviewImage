import KplngiReviewImageInput from './kplngi-reviewimage.input';

const PluginManager = window.PluginManager;
PluginManager.register(
    'KplngiReviewImageInput',
    KplngiReviewImageInput,
    '[data-kplngi-reviewimage-input]'
)

