class Fighters {
    constructor(){
        this.fighter_list_left = [];
        this.fighter_list_right = [];
        //cuvaj podatke o odabrana dva borca
        this.fighter_chosen = [0,1];
        this.toggle = true;
    }

    init(){
        const content = document.querySelectorAll(".fighter-box");
        let i = 0;

        //uvijek je parni broj sa borcima jer su isti sa svake strane, do pola je lijevo i dalje je desno
        const index_mid_low = content.length / 2 - 1;
        const index_mid_high = content.length / 2;
        const index_high = content.length - 1;

        //dohvaca uvijek lijeve pa desne borce
        for(i = 0; i <= index_mid_low; i++){
            this.fighter_list_left.push(content[i]);
        }
        for(i = index_mid_high; i <= index_high; i++){
            this.fighter_list_right.push(content[i]);
        }
        //console.log(this.fighter_list_left);
        //console.log(this.fighter_list_right);

        //lijevi i desni borci click event
        this.fighter_list_left.forEach(fighter_box => {
            this._eventClickHandlerFighter(fighter_box, 0);
        });
        this.fighter_list_right.forEach(fighter_box => {
            this._eventClickHandlerFighter(fighter_box, 1);
        })

        //dodaj click event za tipku nasumicnih boraca
        const buttonRandomFight = document.querySelector("#randomFight");
        this._eventClickHandlerRandomFighters(buttonRandomFight, content.length/2);

        //dodaj click event za samu borbu
        const buttonFight = document.querySelector("#generateFight");
        this._eventClickHandlerFight(buttonFight);
    }

    /**
     * Funkcija koja daje on click event gdje ce se zapoceti postupak borbe
     * @param {*} buttonFight Tipka na kojoj ce se vezati event
     */
    _eventClickHandlerFight(buttonFight){
        buttonFight.addEventListener("click", (e)=>{
            this._fightSimulation();
        });
    }

    /**
     * Funkcija koja daje on click event borcevim div elementima koji sadrze njihove podatke
     * @param {*} fighter_box Sadrzi div element o borcu
     * @param {*} LR Govori koja je strana, 0 za lijevo i 1 za desno
     */
    _eventClickHandlerFighter(fighter_box, LR){
        fighter_box.addEventListener("click", (e) => {
            this._selectFighter(fighter_box, LR);
        });
    }


    /**
     * Funkcija koja daje on click event gdje ce se nasumicno odabrati borci na obje strane.
     * @param {*} buttonRandomFight Tipka na kojoj ce se vezati event
     * @param {*} maxFightersCount Broj dostupnih boraca na jednoj strani
     */
    _eventClickHandlerRandomFighters(buttonRandomFight, maxFightersCount){
        buttonRandomFight.addEventListener("click", (e) => {
            let randomID1, randomID2;
            //generiraj dva broja iz intervala [0, maxFightersCount> sve dok nisu razlicita
            do {
                randomID1 = Math.floor(Math.random() * Math.floor(maxFightersCount));
                randomID2 = Math.floor(Math.random() * Math.floor(maxFightersCount));
            } while (randomID1 == randomID2);
            //ako su definitvno razlicita onda je sigurno raditi sljedece
            this._selectFighter(this.fighter_list_left[randomID1], 0);
            this._selectFighter(this.fighter_list_right[randomID2], 1);
        });
    }
    /**
     * Funkcija koja obraduje simulaciju borbe po specifikaciji
     */
    _fightSimulation(){
        //dohvati i ocisti sve vezano za pocetak borbe
        let temp_fighter_left = this.fighter_chosen[0];
        let temp_fighter_right = this.fighter_chosen[1];
        this._toggleDisableUI();
        const clockUI = document.querySelector("#clock");
        clockUI.innerHTML = 3;
        window.setTimeout((d) => {
            clockUI.innerHTML = 2;
            window.setTimeout((e) => {
                clockUI.innerHTML = 1;
                window.setTimeout((f) => {
                    clockUI.innerHTML = 0;
                    let fighter_left_info = JSON.parse(temp_fighter_left.dataset.info);
                    let fighter_right_info = JSON.parse(temp_fighter_right.dataset.info);
                    //nemoze se dijeliti sa nulom pa nema provjere, u najgorem slucaju se nula dijeli sa nulom sto daje NaN
                    let fighter_left_battle_percentage = fighter_left_info.record.wins / (fighter_left_info.record.wins + fighter_left_info.record.loss);
                    let fighter_right_battle_percentage = fighter_right_info.record.wins / (fighter_right_info.record.wins + fighter_right_info.record.loss);

                    if(fighter_left_battle_percentage == NaN || fighter_right_battle_percentage == NaN){
                        console.log("NaN in fighter battle percentages!!!")
                        return;
                    }

                    let fighter_percentage_difference = fighter_left_battle_percentage - fighter_right_battle_percentage;
                    let prediction = [0.5, 1];

                    //iznad nule znaci da je lijevi borac "bolji"
                    //ispod 10% daje prednost boljem 10%
                    //iznad 10% daje prednost boljem 20%
                    if(fighter_percentage_difference > 0){
                        fighter_percentage_difference = Math.abs(fighter_percentage_difference);
                        if(fighter_percentage_difference >= 0.1){
                            prediction = [0.7, 1];
                        }
                        else if(fighter_percentage_difference < 0.1){
                            prediction = [0.6, 1];
                        }
                    }
                    else{
                        fighter_percentage_difference = Math.abs(fighter_percentage_difference);
                        if(fighter_percentage_difference >= 0.1){
                            prediction = [0.3, 1];
                        }
                        else if(fighter_percentage_difference < 0.1){
                            prediction = [0.4, 1];
                        }
                    }
                    console.log(prediction);
                    let score = Math.random();
                    if(score > prediction[0]){
                        console.log("right cat wins: " + score);
                        this._winFighter(temp_fighter_left, temp_fighter_right, 1);
                    }
                    else{
                        console.log("left cat wins: " + score);
                        this._winFighter(temp_fighter_left, temp_fighter_right, 0);
                    }
                    
                }, 1000);
            }, 1000);
        }, 1000);
    }
    /**
     * Function that handles the end of a fight, LR determines which fighter won (0 for left, 1 for right). Updates the fighters dataset info section on wins and losses.
     * @param {*} left_fighter div element of left fighter
     * @param {*} right_fighter div element of right fighter
     * @param {*} LR which fighter won, 0-left 1-right
     */
    _winFighter(left_fighter, right_fighter, LR){
        let left_fighter_info = JSON.parse(left_fighter.dataset.info);
        let right_fighter_info = JSON.parse(right_fighter.dataset.info);
        const clockUI = document.querySelector("#clock");
        const messageUI = document.querySelector("#message");
        let fighter_picture = document.getElementsByClassName("featured-cat-fighter-image");

        clockUI.innerHTML = "";
        if(!LR){
            messageUI.innerHTML = "Winner is " + left_fighter_info.name + "!";
            fighter_picture[0].style.border = "5px solid green";
            fighter_picture[1].style.border = "5px solid red";
            this._winner(left_fighter, 0);
            this._loser(right_fighter, 1);
        }
        else{
            messageUI.innerHTML = "Winner is " + right_fighter_info.name + "!";
            fighter_picture[1].style.border = "5px solid green";
            fighter_picture[0].style.border = "5px solid red";
            this._winner(right_fighter, 1);
            this._loser(left_fighter, 0);
        }
        this._toggleDisableUI();
        this._disableSymmetricalCharacter(left_fighter, 0);
        this._disableSymmetricalCharacter(right_fighter, 1);
    }

    /**
     * Funkcija koja obavlja rad oko pobjede borca, povecava broj pobjeda u informaciji i sprema ju, te update-a UI za pobjede.
     * @param {*} fighter_winner div element borca pobjednika
     * @param {*} LR Koja strana je pobjednik (0-lijevo, 1-desno)
     */
    _winner(fighter_winner, LR){
        let fighter_info_record_wins = document.getElementsByClassName("wins");

        let fighter_info = JSON.parse(fighter_winner.dataset.info);
        let id = fighter_info.id - 1;

        //bez obzira koja je strana, povecaj na obje strane da se nebi dovelo do kasnijih problema
        let left = this.fighter_list_left[id];//ovako smo osigurali da su definitvno isti borci
        let right = this.fighter_list_right[id];
        //dohvati, promijeni i ponovno zapisi
        let left_info = JSON.parse(left.dataset.info);
        let right_info = JSON.parse(left.dataset.info);
        left_info.record.wins += 1;
        right_info.record.wins += 1;
        left.dataset.info = JSON.stringify(left_info);
        right.dataset.info = JSON.stringify(right_info);
        //update UI za promjene
        fighter_info_record_wins[LR].innerHTML = " " + left_info.record.wins + ",";

        //lv5 dio, povećaj u bazi podataka pobjede za jedan da bi bilo pravilno sinkronizirano
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "updateFighter.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function(){
            if(this.readyState === 4 || this.status === 200){
                console.log(this.responseText);
            }
        }
        xhr.send("id=" + fighter_info.id + "&result=win");
    }

    /**
     *  Funkcija koja obavlja rad oko gubitka borca, povecava broj gubitaka u informaciji i sprema ju, te update-a UI za gubitke.
     * @param {*} fighter_loser div element borca gubitnika
     * @param {*} LR koja strana je gubitnik (0-lijevo, 1-desno)
     */
    _loser(fighter_loser, LR){
        let fighter_info_record_loss = document.getElementsByClassName("loss");

        let fighter_info = JSON.parse(fighter_loser.dataset.info);
        let id = fighter_info.id - 1;

        //bez obzira koja je strana, povecaj na obje strane da se nebi dovelo do kasnijih problema
        let left = this.fighter_list_left[id];//ovako smo osigurali da su definitvno isti borci
        let right = this.fighter_list_right[id];
        //dohvati, promijeni i ponovno zapisi
        let left_info = JSON.parse(left.dataset.info);
        let right_info = JSON.parse(left.dataset.info);
        left_info.record.loss += 1;
        right_info.record.loss += 1;
        left.dataset.info = JSON.stringify(left_info);
        right.dataset.info = JSON.stringify(right_info);
        //update UI za promjene
        fighter_info_record_loss[LR].innerHTML = " " + left_info.record.loss;

        //lv5 dio
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "updateFighter.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function(){
            if(this.readyState === 4 || this.status === 200){
                console.log(this.responseText);
            }
        }
        xhr.send("id=" + fighter_info.id + "&result=loss");
    }

    /**
     * Funkcija koja onesposobi cijeli UI dostupan korisniku, koristi se unutar borbe da se ne bi mogao mijenjati borac usred odbrojavanja.
     */
    _toggleDisableUI(){
        const buttonFight = document.querySelector("#generateFight");
        const buttonRandomFight = document.querySelector("#randomFight");
        //ako je toggle true onda je enabled sve, pa se mora disable-at i obrnuto
        if(this.toggle){
            buttonFight.disabled = true;
            buttonRandomFight.disabled = true;
            //.pointerEvents gasi/pali on click evente za elemente koje nisu tipke
            this.fighter_list_left.forEach(element => {
                element.style.pointerEvents = 'none';
                element.style.opacity = '0.75';
            });
            this.fighter_list_right.forEach(element => {
                element.style.pointerEvents = 'none';
                element.style.opacity = '0.75';
            });
            this.toggle = !(this.toggle);
        }
        else{
            buttonFight.disabled = false;
            buttonRandomFight.disabled = false;
            this.fighter_list_left.forEach(element => {
                element.style.pointerEvents = 'auto';
                element.style.opacity = '1';
            });
            this.fighter_list_right.forEach(element => {
                element.style.pointerEvents = 'auto';
                element.style.opacity = '1';
            });
            this.toggle = !(this.toggle);
        }
    }

    /**
     * Funkcija koja odabire borca za jednu stranu te obraduje sve vezano za to
     * @param {*} fighter_box Sadrzi div element o borcu
     * @param {*} LR Govori koja je strana, 0 za lijevo i 1 za desno
     */
    _selectFighter(fighter_box, LR){
        //dohvati podatke o borcu
        let cat_info = JSON.parse(fighter_box.dataset.info);
        //dohvati info elemente, dolaze u parovima lijevo/desno
        let fighter_info_name = document.getElementsByClassName("list-group-item name");
        let fighter_info_age = document.getElementsByClassName("list-group-item age");
        let fighter_info_skills = document.getElementsByClassName("list-group-item skills");
        let fighter_info_record_wins = document.getElementsByClassName("wins");
        let fighter_info_record_loss = document.getElementsByClassName("loss");
        let fighter_picture = document.getElementsByClassName("featured-cat-fighter-image");

        //0 je za lijeve, 1 za desne --> LR
        //u ovom dijelu se postavljaju podatci pokraj slike i sama slika
        fighter_info_name[LR].innerHTML = "Cat name: " + cat_info.name;
        fighter_info_age[LR].innerHTML = "Cat age: " + cat_info.age;
        fighter_info_skills[LR].innerHTML = "Cat info: " + cat_info.catInfo;
        fighter_info_record_wins[LR].innerHTML = " " + cat_info.record.wins + ",";
        fighter_info_record_loss[LR].innerHTML = " " + cat_info.record.loss;
        fighter_picture[LR].src = fighter_box.childNodes[1].src;
        fighter_picture[0].style.border = "0px solid black";
        fighter_picture[1].style.border = "0px solid black";
        this.fighter_chosen[LR] = fighter_box;
        this._disableSymmetricalCharacter(fighter_box, LR);

        //ako su oba borca odabrana omoguci tipku
        if(this.fighter_chosen[0] != 0 && this.fighter_chosen[1] != 1){
            document.querySelector("#generateFight").disabled = false;
        }
    }

    /**
     * Funkciji se predaje borac i s koje strane borac dolazi te onesposobljava se taj isti borac na suprotnoj strani.
     * @param {*} fighter_box Sadrzi div element borca
     * @param {*} LR Govori koja je strana, 0 za lijevo i 1 za desno
     */
    _disableSymmetricalCharacter(fighter_box, LR){
        let fighter_info = JSON.parse(fighter_box.dataset.info);
        let internal_id = fighter_info.id - 1;
        let oposite_fighter;
        //ovisno koja strana se mijenja resetiraj sve podatke na normalno prije djelovanja na jednog pojedinca
        //.style.pointerEvents je style komponenta koja govori dali element moze triggerati evente, 'auto' za normalno ponasanje i 'none' za ne
        if(LR == 0){
            oposite_fighter = this.fighter_list_right[internal_id];
            this.fighter_list_right.forEach(element => {
                element.style.pointerEvents = 'auto';
                element.style.opacity = '1';
            });
            this.fighter_list_left.forEach(element => {
                element.style.border = '0px solid black';
            });
        }
        else{
            oposite_fighter = this.fighter_list_left[internal_id];
            this.fighter_list_left.forEach(element => {
                element.style.pointerEvents = 'auto';
                element.style.opacity = '1';
            });
            this.fighter_list_right.forEach(element => {
                element.style.border = '0px solid black';
            });
        }
        //djeluj sad na pojedinca nakon reseta izgleda
        //daj suprotnome prozirnost i onemoguci evente
        oposite_fighter.style.pointerEvents = 'none';
        oposite_fighter.style.opacity = '0.75';

        //daj trenutnome uocljiv rub
        fighter_box.style.border = '2px solid red';
    }
}
//onesposobljavanje tipke dok se ne odaberu macke
document.querySelector("#generateFight").disabled = true;

const fighters = new Fighters;
fighters.init();