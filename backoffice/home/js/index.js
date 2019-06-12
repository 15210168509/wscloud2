/**
 * Created by Thinkpad on 2018/5/31.
 */
$(document).ready(function(){
    slidr.create('slidr-home', {

        after: function(e) { console.log('in: ' + e.in.slidr); },

        before: function(e) { console.log('out: ' + e.out.slidr); },

        breadcrumbs: true,

        controls: 'none',

        direction: 'horizontal',

        fade: false,

        keyboard: true,

        overflow: false,

        pause: false,

        theme: '#222',

        timing: { 'cube': '0.5s ease-in' },

        touch: true,

        transition: 'cube'

    }).start().auto();
});
