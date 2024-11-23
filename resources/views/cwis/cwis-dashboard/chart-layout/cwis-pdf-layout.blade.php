<!-- Last Modified Date: 24-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
<style>
    h1 {
        margin: 0;
        font-weight: normal;
        font-size: 36px;
        width: 100%;

    }
    h2{
        font-weight: normal;
    }
    .tabs {
       padding:20px;
    }

    .card-header {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
    }

    .input {
        position: absolute;
        opacity: 0;
    }

    .label {
        width: 100%;
        padding: 20px 30px;

        cursor: pointer;
        font-weight: bold;
        font-size: 18px;
        color: #7f7f7f;

        height: 50%;
    }

    .label:hover {
        background: #d8d8d8;
    }

    .label:active {
        background: #ccc;
    }

    .input:focus+.label {
        z-index: 1;
    }

    .input:checked+.label {
        background: #fff;
        color: #000;
    }

    @media (min-width: 600px) {
        .label {
            width: auto;
        }
    }

    .panel {
        display: block;
        padding: 20px 30px 30px;

        width: 100%;
        /* Set panel width to 100% */
    }

    @media (min-width: 600px) {

        .panel {
            display: block;
            padding: 20px 30px 30px;

            width: 100%;
            /* Set panel width to 100% */
            order: 99;

        }
    }

    .input:checked+.label+.panel {
        display: block;

    }

    @media (max-width: 600px) {
        .input:checked+.label+.panel {
            display: none;
            /* Show the panel when the input is checked on smaller screens */
        }
    }





    /* Additional styles for larger screens if needed */
    @media screen and (max-width: 540px) {
    .chart__figure {
        flex-direction: column;
        height: auto;
    }

    .chart__caption {
        margin: 15px auto auto;
        text-align: center;
        min-width: 160px;
    }

    .chart {
        width: 100%;
        margin-right: 0;
    }
}

/* Fonts (Google fonts) */
.font--barlow {
    font-family: "Barlow Condensed", sans-serif;
}

.font--montserrat {
    font-family: "Montserrat", sans-serif;
}

.color--grey {
    color: #334466;
}

.color--green {
    color: #01713c;
}

/* Values */
.canvas-size {
    width: 160px;
    height: 50px;
}

.font-weight--900 {
    font-weight: 900;
}

.animation-time--1400ms {
    animation-duration: 1400ms;
}

/* Fading animation */
@keyframes fadein {
    0% {
        opacity: 0;
    }

    40% {
        opacity: 0;
    }

    80% {
        opacity: 1;
    }

    100% {
        opacity: 1;
    }
}

.main {
    display: grid;
}

.chart {
    position: relative;
    font-weight: 500;
    margin-right: 80px;
    /* Adjust the margin as needed */
    width: 50%;

    @media screen and (max-width: 540px) {
        width: 100%;
        margin-right: 0;
    }

    .chart__figure {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        height: 140px;
        /* Adjust the height as needed */

        @media screen and (max-width: 540px) {
            flex-direction: column;
            height: auto;
            margin-bottom: 0;
        }
    }

    .chart__canvas {
        margin: auto;
        width: 160px;
        height: 140px;
        /* Adjust the height as needed */
    }

    .chart__caption {
        display: flex;
        justify-content: center;
        flex-direction: column;
        margin-left: 30px;
        font-size: 36px;
        line-height: 56px;
        height: 100%;
        width: calc(80px + 160px);
        color: #01713c;

        @media screen and (max-width: 540px) {
            margin: 15px auto auto;
            text-align: center;
            min-width: 160px;
        }
    }

    .chart__value {
        display: grid;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        height: calc(40px + 160px);
        width: 160px;
        animation: fadein 1400ms;
        text-align: center;
        /* Center the text */

        @media screen and (max-width: 540px) {
            left: 0;
            right: 0;
            width: 100%;
        }

        p {
            font-size: 20px;
            margin: auto;
            font-family: "Barlow Condensed", sans-serif;
        }
    }
}

.hi {
    display: flex;
    flex-wrap: wrap;

}

/* Styles for each card container */
.card {
    border: 1px solid rgb(231, 227, 227);
    width: 420px;
    height: 350px;
    margin: 10px 10px;
    display: grid;
    padding: 2px;
    place-items: center;
    background-color: #F4F9F7;
}

.card4 {
    width: 825px;
    place-items: none;
}

.card img {
    width: 200px;
    height: 150px;
}

.card-header {
    width: 100%;
    text-align: left;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #ccc;
}

/* heading */
.card h2 {
    font-size: 12px;
}

/* paragraph */
.card p {
    font-size: 15px;
}

span {
    position: absolute;
    top: 50%;
    left: 50%;
    text-align: center;
    font-size: 30px;
    margin-left: -25px;
    margin-top: -20px;
}

.chart-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}
.safety {
        border: 1px solid rgb(231, 227, 227);
        width: 400px;
        height: 350px;
        margin: 10px 10px;
        display: grid;
        padding: 2px;
        place-items: center;
        background-color: #F4F9F7;
    }

    .safety1, .safety2, .safety3, .safety4 {
        width: 100%;
        height: auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .sf1, .sf2, .sf3, .sf4 {
        width: calc(33.33% - 20px);
        margin-bottom: 20px;
        box-sizing: border-box;
    }

    .sf1:last-child, .sf2:last-child,.sf3:last-child, .sf4:last-child {
        margin-right: auto;
        margin-left: 2.5%;
    }

    .sf1 img, .sf2 img, .sf3 img, .sf4 img {
        width: 100%;
        height: auto;
        margin-bottom: 10px;
    }

    .safety img {
        width: 200px;
        height: 180px;
    }

    .card-header {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
    }

    /* heading */
    .safety h2 {
        font-size: auto;
    }

    /* paragraph */
    .safety p {
        font-size: 15px;
        text-align: center;
    }
    .sus {
        border: 1px solid rgb(231, 227, 227);
        width: 400px;
        height: 350px;
        margin: 10px 10px;
        display: grid;
        padding: 2px;
        place-items: center;
        background-color: #F4F9F7;
    }

    .sus img {
        width: 200px;
        height: 180px;
    }

    .sus-header {
        width: 100%;
        text-align: left; /* Adjusted alignment to left */
        margin-bottom: 10px;
        padding-bottom: 5px; /* Added padding for space between border and text */
        border-bottom: 1px solid #ccc; /* Added border-bottom */
    }

    /* heading */
    .sus h2 {
        font-size: 12px;
    }

    /* paragraph */
    .sus p {
        font-size: 15px;
    }


</style>
<div class="container">
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-imis.png'))) }}" class="logo" style=" width: 120px;">
       <div class="header">
           <h1 class="heading" style="text-transform:uppercase; margin: 0; text-align:center">Municipality</h1>
           <h2 style="text-transform:uppercase; margin: 10px; text-align:center ">CWIS Indicator</h2>

       </div>
   <hr>


   </div>
<div class="tabs" style="margin: 5px;">
        <input class="input" name="tabs" type="radio" id="tab-1" checked="checked" />
        <label class="label" for="tab-1"></label>
        <div class="" >
            <h1>Equity</h1>
            <div class="card card1" > <div class="card-header" style="font-size: 15px">
                % of LIC population with access to safe individual toilets / % of total population
                with access  to safe individual toilets
              </div>


                <div style="margin-left:25%">
                    <h2 style="color: #6CB4EE; font-weight: bold; font-size: 100px; margin-top:25% ">{{$eq1}}</h2>
                </div>


        </div>
            <h1 >Safety</h1>
            <div class="hi" >
                <div class="safety safety1" >
                    <div class="card-header">
                        Safely managed sanitation (citywide IHHL)

                            </div>
                    <br>

                    <div class="sf1">
                        <div class="chart" id="sf1aContainer">
                            <figure class="chart__figure">

                                 <img  src="data:image/png;base64,{{ $encodedDatasf1a }}" alt="Image">

                            </figure>
                        </div>

                        <p>Population with access to safe individual toilets</p>
                    </div>
                    <div class="sf1">
                        <div class="chart" id="sf1bContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf1b }}" alt="Image">

                            </figure>
                        </div>

                        <p>IHHL OSSs that have been desludged</p>
                    </div>
                    <div class="sf1">

                        <div class="chart" id="sf1cContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf1c }}" alt="Image">

                            </figure>
                        </div>

                        <p>Collected FS disposed at treatment plant or designated disposal site</p>
                    </div>
                    <div class="sf1">
                        <div class="chart" id="sf1dContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf1d }}" alt="Image">


                            </figure>
                        </div>

                        <p>FS treatment capacity as a % of total FS generated from non-sewered connections</p>
                    </div>
                    <div class="sf1">
                        <div class="chart" id="sf1eContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf1e }}" alt="Image">


                            </figure>
                        </div>

                        <p>FS treatment capacity as a % of volume disposed of at the treatment plant</p>
                    </div>
                    <div class="sf1">

                        <div class="chart" id="sf1fContainer">
                            <figure class="chart__figure">

                                 <img  src="data:image/png;base64,{{ $encodedDatasf1f }}" alt="Image">

                            </figure>
                        </div>

                        <p>WW treatment capacity as a % of total WW generated from sewered connections and greywater and supernatant generated from non-sewered connections</p>
                    </div>
                    <div class="sf1" style="margin-top: 2.5%">
                        <div class="chart" id="sf1gContainer">
                            <figure class="chart__figure">

                                 <img  src="data:image/png;base64,{{ $encodedDatasf1g }}" alt="Image">

                            </figure>
                        </div>

                        <p>WW treatment capacity as a % of total WW generated from sewered connections and greywater and supernatant generated from non-sewered connections</p>
                    </div>
                </div>

                <div class="safety safety2" style=" margin-top: 2%">
                    <div class="card-header">
                        Safely managed sanitation for LIC IHHL
                    </div>
                    <br>

                    <div class="sf2">
                        <div class="chart" id="sf2aContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf2a }}" alt="Image">


                            </figure>
                        </div>

                        <p>Low income community (LIC) population with access to safe individual toilets</p>
                    </div>
                    <div class="sf2">
                        <div class="chart" id="sf2bContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf2b }}" alt="Image">

                            </figure>
                        </div>

                        <p>LIC OSSs that have been desludged</p>
                    </div>
                    <div class="sf2">

                        <div class="chart" id="sf2cContainer">
                            <figure class="chart__figure">

                                 <img  src="data:image/png;base64,{{ $encodedDatasf2c }}" alt="Image">

                            </figure>
                        </div>

                        <p>FS collected from LIC that is disposed at treatment plant or designated disposal site</p>
                    </div>
                </div>

                <div class="safety safety3" style="margin-top: 2%">
                    <div class="card-header">
                        Safely managed liquid waste for shared facilities (CT & shared household
                        toilets)
                    </div>
                    <br>

                    <div class="sf3">
                        <div class="chart" id="sf3aContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf3a }}" alt="Image">

                            </figure>
                        </div>

                        <p>Dependent population (without IHHL) with access to safe shared facilities</p>
                    </div>
                    <div class="sf3">
                        <div class="chart" id="sf3bContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf3b }}" alt="Image">

                            </figure>
                        </div>

                        <p>Shared facilities that adhere to principles of universal design</p>
                    </div>
                    <div class="sf3">

                        <div class="chart" id="sf3cContainer">
                            <figure class="chart__figure">

                                 <img  src="data:image/png;base64,{{ $encodedDatasf3c }}" alt="Image">
                            </figure>
                        </div>

                        <p>Shared facility users who are women</p>
                    </div>
                    <div class="" style="margin-bottom: 0%">
                        <div style="margin-left:25%">
                            <h2 style="color: #29ab87; font-weight: bold; font-size: 100px; margin-top:5%; margin-right:20% ">{{$sf3d}}</h2>
                        </div>
                        <p>Average distance from HH to shared facility (m)</p>
                    </div>

                </div>


                <div class="safety safety4" style="margin-top:11%">
                    <div class="card-header">
                        Public spaces that have adequate sanitation facilities (Public Toilets/PT)
                    </div>
                    <br>

                    <div class="sf4">
                        <div class="chart" id="sf4aContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf4a }}" alt="Image">

                            </figure>
                        </div>

                        <p>PT where FS/WW generated is safely transported to TP or safely disposed in situ</p>
                    </div>
                    <div class="sf4">
                        <div class="chart" id="sf4bContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf4b }}" alt="Image">

                            </figure>
                        </div>

                        <p>PT that adhere to principles of universal design</p>
                    </div>
                    <div class="sf4">

                        <div class="chart" id="sf4dContainer">
                            <figure class="chart__figure">
                                 <img  src="data:image/png;base64,{{ $encodedDatasf4d }}" alt="Image">
                            </figure>
                        </div>

                        <p>PT users who are women</p>
                    </div>

                </div>
                <div class="safety safety5"  style="margin-top:3%">
                    <div class="card-header">
                        Educational institutions where FS generated is safely transported to TP or safely disposed in situ
                    </div>
                    <div class="chart" id="sf5Container">
                        <figure class="chart__figure">
                             <img  src="data:image/png;base64,{{ $encodedDatasf5 }}" alt="Image">

                        </figure>
                    </div>

                </div>

                <div class="safety safety6" style="margin-top:3%">
                    <div class="card-header">
                        Desludging services completed mechanically or semi-mechanically
                    </div>
                    <div class="chart" id="sf6Container">
                        <figure class="chart__figure">

                             <img  src="data:image/png;base64,{{ $encodedDatasf6 }}" alt="Image">
                        </figure>
                    </div>

                </div>


                <div class="safety safety7" style="margin-top:3%">
                    <div class="card-header">
                        Desludging services completed mechanically or semi-mechanically
                    </div>
                    <div class="chart" id="sf7Container">
                        <figure class="chart__figure">

                             <img  src="data:image/png;base64,{{ $encodedDatasf7 }}" alt="Image">
                        </figure>
                    </div>


                </div>


              </div>
            <h1 style="margin-top:25%">Sustainability</h1>

            <div class="hi">

            <div class="sus sus1" style="margin-top: 2.5%">
                <div class="card-header">
                    <h2> Treated FS that is reused</h2>
                </div>
                <div class="chart" id="ss1Contain">
                    <figure class="chart__figure">
                         <img  src="data:image/png;base64,{{ $encodedDatass1 }}" alt="Image">

                    </figure>
                </div>

            </div>
            </div>
        </div>




</div>
