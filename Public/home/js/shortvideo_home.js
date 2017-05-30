(function () {
    new Swiper('#divBannerList', {
        pagination: '#divBannerPagination',
        paginationClickable: true
    });

    new Swiper('#divHotList', {
        slidesPerView: 3.3,
        spaceBetween: 7,
        paginationClickable: true
    });

    new Swiper('#divStandpointList', {
        slidesPerView: 2.3,
        spaceBetween: 7,
        paginationClickable: true
    });

    new Swiper('#divHumorList', {
        slidesPerView: 2.3,
        spaceBetween: 7,
        paginationClickable: true
    });

    new Swiper('#divLifeList', {
        slidesPerView: 2.3,
        spaceBetween: 7,
        paginationClickable: true
    });
})();