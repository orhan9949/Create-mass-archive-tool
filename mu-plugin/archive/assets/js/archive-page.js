import { store, getContext } from '@wordpress/interactivity';
import Papa from '@cp/papaparse';
const { actions } = store('archivePagePlugin', {

    actions: {

        preloader(context) {
            context.preloaderWidth = Math.floor( Number(context.idsArchive.length) / Number(context.ids.length ) * 100 ) + '%';
        },

        getFile(e) {
            const context = getContext();
            const file = e.target.files[0];
            if (!file) {
                return;
            }
            context.fileData = file;
            actions.getDataCsv(context.fileData);
        },

        checkFile(e){
            e.preventDefault();
            const context = getContext();
            context.preloaderWidth = "0%";
            context.idsArchive = [];
            context.preloaderText= 'Start';
            context.result = [];
            context.countItem = 1;
            actions.processArchivated(context);

        },

        getDataCsv (file) {
            const context = getContext();
            context.nameInput = file.name;
            Papa.parse(file, {
                header: true,
                dynamicTyping: true,
                complete: function(results) {
                    results.data.forEach( result => {
                        if (result['Post ID'] !== null) {
                            context.data.push(result);
                            context.ids.push(result['Post ID']);
                        }
                    })
                }
            });
        },

        scrollBottom(context) {
            context.countItem = context.countItem + 10;
            const box = document.querySelector(".archive-page__item-body");
            box.scrollTop = box.scrollHeight;
        },

        async processArchivated(context) {
            for (let i = 0; i < context.data.length; i += 10) {
                const chunk = context.data.slice(i, i + 10);
                await actions.apiFetch(chunk, context);
                actions.scrollBottom(context);
            }
        },

        async apiFetch(dataPosts, context, newStatus = 'archive') {
            context.preloaderText = 'Loading('+context.preloaderWidth+')...';
            return await fetch('/wp-json/crypto/v1/update-posts-status/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': context.nonce
                },
                body: JSON.stringify({
                    data_posts: dataPosts,
                    new_status: newStatus
                })
            })
            .then(response => {
                return response.json();
            })
            .then(result => {
                let count = context.countItem;
                result.posts.forEach(post => {
                    post.count = count;
                    count++;
                    context.idsArchive.push(post["Post ID"]);
                    context.result.push(actions.newPostObject(post));
                    actions.preloader(context);
                });
                context.preloaderText = 'Finish';
                return true;
            })
            .catch(error => {
                console.error('Error update:', error);
                throw error;
            });
        },

        newPostObject(postData) {
            return {
                count: postData['count'],
                post_id: postData['Post ID'],
                url: postData['URL'],
                redirect_url: postData['Redirect URL'],
            };
        }
    }
});

