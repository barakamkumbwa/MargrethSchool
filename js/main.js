/* =========================================
   MARGRETH PRE & PRIMARY SCHOOL
   Main JavaScript File
   ========================================= */

document.addEventListener("DOMContentLoaded", function() {
    
    // Hapa utaweka JavaScript nyingine baadaye
    // (Kama FAQ Accordion, Form Validation, n.k.)
    
    console.log("Margreth School Website Loaded Successfully!");

});
    /* --- 2. ACCORDION FUNCTIONALITY (Card Slide Animation) --- */
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        
        header.addEventListener('click', () => {
            
            // Kama kitu hiki kimefunguliwa (active)
            if (item.classList.contains('active')) {
                
                // 1. Ongeza class ya 'closing' kwa card nzima
                item.classList.add('closing');
                item.classList.remove('active');
                
                // 2. Baada ya animation (0.5s), ondoa 'closing'
                setTimeout(() => {
                    item.classList.remove('closing');
                    
                    // 3. Fungua card ya kwanza kwa animation ya 'opening'
                    const firstItem = accordionItems[0];
                    firstItem.classList.add('opening');
                    
                    setTimeout(() => {
                        firstItem.classList.remove('opening');
                        firstItem.classList.add('active');
                    }, 100);
                    
                }, 500);
                
            } else {
                
                // 1. Kama kuna card nyingine imefunguliwa, ifunge kwa animation
                accordionItems.forEach(otherItem => {
                    if (otherItem.classList.contains('active')) {
                        otherItem.classList.add('closing');
                        otherItem.classList.remove('active');
                        
                        setTimeout(() => {
                            otherItem.classList.remove('closing');
                        }, 500);
                    }
                });
                
                // 2. Fungua card uliyobonyeza kwa animation ya 'opening' baada ya muda
                setTimeout(() => {
                    item.classList.add('opening');
                    
                    setTimeout(() => {
                        item.classList.remove('opening');
                        item.classList.add('active');
                    }, 100);
                    
                }, 500);
            }
        });
    });