let SS88_VUM = {

    init: ()=>{

        SS88_VUM.initToggle();
		SS88_VUM.deleteBtns();
        
    },
    initToggle: ()=>{

        let OnorOff = localStorage.getItem('SS88-VUM');

        if(OnorOff==null) {
            
            OnorOff = true;
            localStorage.setItem('SS88-VUM', OnorOff);

        }

        OnorOff = (OnorOff==='false') ? false : true;

        SS88_VUM.toggleView(OnorOff);
        SS88_VUM.toggleToggle(OnorOff);

        document.querySelector('#SS88VUM-toggle').addEventListener('change', (e) => {

            SS88_VUM.toggleView(e.target.checked);
            SS88_VUM.toggleToggle(e.target.checked);
            SS88_VUM.store(e.target.checked);

            if(e.target.checked) {

                setTimeout(()=>{
                
                    const element = document.getElementById('SS88-VUM-table-wrapper');
                    const y = element.getBoundingClientRect().top + window.pageYOffset + -100;
                    window.scrollTo({top: y, behavior: 'smooth'});
                
                }, 200);

            }

        });

        if(document.querySelector('#acf-extended-admin-css')) document.querySelector('#SS88-VUM-table-wrapper').classList.add('has-acfe');

    },
    toggleView: (checked) =>{

        document.querySelector('#SS88-VUM-table-wrapper').style.display = (checked) ? 'block' : 'none';

    },
    toggleToggle: (checked) =>{

        document.querySelector('#SS88VUM-toggle').checked = checked;

    },
    store: (checked) => {
        
        if(checked===true || checked===false) localStorage.setItem('SS88-VUM', checked);

    },
	deleteBtns: () => {

		document.querySelectorAll('button.btn-delete[data-key]').forEach((button)=>{

			button.addEventListener('click', (e) => {

				e.preventDefault();

				if( confirm(SS88_VUM_translations.confirm_delete) ) {

					fetch(ajaxurl, {

						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams(requestData = { action: 'SS88_VUM_delete', key: button.dataset.key, uid: button.dataset.uid }).toString(),
					
					}).then(function(response) {
	
						return response.json();
	
					}).then(function(response) {
	
						if(response.success) {
	
							alert(SS88_VUM_translations.success + ' ' + response.data.body);
							button.parentElement.parentElement.remove();
	
						}
						else {
	
							alert(SS88_VUM_translations.error + ' ' + response.data.httpcode +': ' + response.data.body);
	
						}
	
					}).catch( err => { console.log(err); alert(SS88_VUM_translations.error + ' ' + err.message); } );

				}
	
			});

		});

	}

}

window.addEventListener('DOMContentLoaded', (event) => {

	if(document.querySelector('#SS88-VUM-table-wrapper')) SS88_VUM.init();

});