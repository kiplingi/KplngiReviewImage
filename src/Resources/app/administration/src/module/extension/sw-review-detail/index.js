import template from './sw-review-detail.html.twig';
import './sw-review-detail.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.override('sw-review-detail', {
    template,

    mixins: [
        'placeholder',
        'notification',
        'salutation'
    ],

    data() {
        return {
            media: {}
        }
    },

    computed: {
        kplngiReviewMediaRepository() {
            return this.repositoryFactory.create('kplngi_review_image_media');
        }
    },

    watch: {
        review: {
            handler(review) {
                const criteria = new Criteria();
                criteria.addAssociation('media');

                criteria.addFilter(Criteria.equals('reviewId', review.id));

                this.kplngiReviewMediaRepository.search(
                    criteria,
                    Shopware.Context.api
                ).then((result) => {
                    if (result.total > 0) {
                        this.media = result.first().media;
                    }
                });
            }
        }
    }
});
