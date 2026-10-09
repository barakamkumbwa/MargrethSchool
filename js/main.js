/* =========================================
   MARGRETH PRE & PRIMARY SCHOOL
   Main JavaScript File
   ========================================= */

document.addEventListener("DOMContentLoaded", function() {
    
    // Hapa utaweka JavaScript nyingine baadaye
    // (Kama FAQ Accordion, Form Validation, n.k.)
    
    console.log("Margreth School Website Loaded Successfully!");

});

     /* --- 2. ACCORDION FUNCTIONALITY --- */
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        
        header.addEventListener('click', () => {
            
            // Kama kitu hiki kimefunguliwa (active)
            if (item.classList.contains('active')) {
                // Kifunge
                item.classList.remove('active');
                
                // Kisha fungua kitu cha kwanza (Our History) automatically
                accordionItems[0].classList.add('active');
                
            } else {
                // Kama kimefungwa, funga vingine vyote
                accordionItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                });
                
                // Kisha fungua kitu ulichobonyeza
                item.classList.add('active');
            }
        });
    });