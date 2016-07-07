jQuery(document).ready( function($) {

    /**************************************************************/
    /* Prepares the cv to be dynamically expandable/collapsible   */
    /**************************************************************/
    $(function prepareList() {
        // Hide all modules as default
        $('.expListProg').find('li:not(.course-progress-module)')

            .addClass('collapsed')
            .hide();

        // Show current module
        $('.expListProg').find('li.current').prevUntil('.course-progress-module').andSelf().nextUntil('.course-progress-module').andSelf().toggleClass('collapsed').toggleClass('expanded')
            .show();

        // toggle current module arrow
        $('.expListProg').find('li.current').prevAll('.course-progress-module:first').find('.tog-mod').toggleClass('fa-chevron-down fa-chevron-up');

        // toggle expand/collapse on module div click
        $('.expListProgMain').unbind('click').click(function (event) {
            if (this == event.target) {
                $(this).nextUntil('.course-progress-module').toggleClass('collapsed expanded');

                $(this).nextUntil('.course-progress-module').toggle('medium');
                $(this).find('.tog-mod').toggleClass('fa-chevron-down fa-chevron-up');
            }
            return false;
        });

        // toggle expand/collapse on module title and arrow click
        $('.expList3').unbind('click').click(function (event) {
            if (this == event.target) {
                $(this).closest('.expListProgMain').nextUntil('.course-progress-module').toggleClass('collapsed expanded');
                $(this).closest('.expListProgMain').nextUntil('.course-progress-module').toggle('medium');

                $(this).parent().find('.tog-mod').toggleClass('fa-chevron-down fa-chevron-up');
            }
            return false;
        });

        //Hack to add links inside the cv
        $('.expListProg a').unbind('click').click(function () {

            window.open($(this).attr('href'), '_self');
            return false;
        });


        /**************************************************************/
        /* Functions to execute on loading the document               */
        /**************************************************************/
        document.addEventListener('DOMContentLoaded', function () {
            prepareList();
        }, false);

    });
});
