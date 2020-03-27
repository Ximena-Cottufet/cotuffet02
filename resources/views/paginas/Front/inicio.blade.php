@extends('layouts.cuerpo')

@section('titulo','.: Cotuffet Development :. Inicio :.')

@section('cuerpo')
      <style>
            #Cotuffet3DViewer {
                  width: 100%;
                  height: 430px;
                  float: left;
            }
      </style>
      <section class="container-fluid" id="contenedor1">
        <!--<div class="social">
               <ul>
                    <li><a href="  " class="icon-facebook">Conoce más productos</a><img src="../img/Front/Iconos/mueble.png" alt="" class="img-icon"></li>
                    <li><a href="#" class="icon-facebook">Decora desde tu celular</a><img src="../img/Front/Iconos/tactil.png" alt="" class="img-icon"></li>
               </ul>
          </div>-->
          <div class="row justify-content-center">
              <div class="col-12 col-md-8 envolturaInicial visorInicial3D"> <!-- Inicio del Configurador -->
                <div class="row">
                    <div class="col-12 col-lg-7">
                        <div id="Cotuffet3DViewer"></div>
                        <script type="module">

                            //------------------------++------------------------

                            //--------------------------------------------------
                            //        IDs de Contenedores
                            //--------------------------------------------------
                            const viewer3D = "Cotuffet3DViewer";
                            const viewer3DControls = "Cotuffet3DControls";
                            const viewer3DSelector = "Cotuffet3DSelector";

                            //--------------------------------------------------
                            //        Librerias de ThreeJS
                            //--------------------------------------------------
                            import * as THREE from '../js/three/three.module.js';
                            import { OrbitControls } from '../js/three/OrbitControls.js';
                            import { FBXLoader } from '../js/three/FBXLoader.js';
                            //import { GLTFLoader } from '../js/three/GLTFLoader.js';
                            //--------------------------------------------------

                            //        Variables Globales
                            //--------------------------------------------------
                            var container, controls;
                            var camera, scene, renderer, light, mixer, clock;
                            var objectData, object3D, object3DID;

                            //--------------------------------------------------
                            //        Funcion Principal
                            //--------------------------------------------------
                            $(document).ready(function () {
                                init();
                                animate();

                                $.getJSON("../js/objects.json", function (data) {
                                    objectData = data.slice();
                                    LoadPreviews(objectData);
                                    LoadModel(objectData, 0);
                                })
                            });

                            //--------------------------------------------------
                            //   Crea escena:
                            //   Integra todos los elementos requeridos:
                            //   camara, luces, suelo, niebla y malla
                            //--------------------------------------------------
                            function init() {
                                container = $("#" + viewer3D);

                                scene = new THREE.Scene();
                                scene.background = new THREE.Color(0xa0a0a0);
                                //scene.fog = new THREE.Fog(0xa0a0a0, 100, 5000);

                                camera = new THREE.PerspectiveCamera(45, container.width() / container.height(), 1, 20000);
                                camera.position.set(1700, 2000, 1700);

                                light = new THREE.HemisphereLight(0xffffff, 0x444444);
                                light.position.set(0, 100, 0);
                                scene.add(light);

                                light = new THREE.DirectionalLight(0xffffff);
                                light.position.set(0, 1000, 0);
                                light.castShadow = true;
                                light.shadow.camera.top = 1800;
                                light.shadow.camera.bottom = - 1000;
                                light.shadow.camera.left = - 1200;
                                light.shadow.camera.right = 1200;
                                scene.add(light);

                                var floor = new THREE.Mesh(new THREE.PlaneBufferGeometry(5000, 5000), new THREE.MeshPhongMaterial({ color: 0x999999, depthWrite: false }));
                                floor.rotation.x = - Math.PI / 2;
                                floor.receiveShadow = true;
                                scene.add(floor);

                                var grid = new THREE.GridHelper(5000, 50, 0x000000, 0x000000);
                                grid.material.opacity = 0.2;
                                grid.material.transparent = true;
                                scene.add(grid);

                                renderer = new THREE.WebGLRenderer({ antialias: true });
                                renderer.setPixelRatio(window.devicePixelRatio);
                                renderer.setSize(container.width(), container.height());
                                renderer.shadowMap.enabled = true;
                                container.append(renderer.domElement);

                                controls = new OrbitControls(camera, renderer.domElement);
                                controls.target.set(0, 500, 0);
                                controls.update();

                                clock = new THREE.Clock();
                                window.addEventListener('resize', onWindowResize, false);
                            }

                            //--------------------------------------------------
                            //   Recalcula nuevo tamaño del visor si cambia
                            //   el tamaño de la pantalla
                            //--------------------------------------------------
                            function onWindowResize() {
                                camera.aspect = container.width() / container.height();
                                camera.updateProjectionMatrix();
                                renderer.setSize(container.width(), container.height());
                            }

                            //--------------------------------------------------
                            //   Controlador de Animacion
                            //--------------------------------------------------
                            function animate() {
                                requestAnimationFrame(animate);
                                var delta = clock.getDelta();
                                if (mixer) mixer.update(delta);
                                renderer.render(scene, camera);
                            }

                            //--------------------------------------------------
                            //   Carga modelo especificado
                            //--------------------------------------------------
                            function LoadModel(objectData, id) {
                                var data = objectData[id].data;
                                var corrections = objectData[id].corrections;
                                var texturizable = objectData[id].texturizable;

                                // Crea objeto 3D seleccionado por el indice
                                // TODO: agregar If para detectar si el objeto es FBX o GLTF
                                //var loader = new GLTFLoader();
                                var loader = new FBXLoader();
                                loader.load(data.url + data.filename, function (object) {
                                    console.log("loading:" + data.url + data.filename);

                                    //mixer = new THREE.AnimationMixer(object);
                                    //var action = mixer.clipAction(object.animations[0]);
                                    //action.play();

                                    ApplyTextures(object, data.url, texturizable);
                                    object.scale.set(corrections.scale, corrections.scale, corrections.scale);
                                    object3D = object;
                                    object3DID = id;
                                    scene.add(object);
                                });

                                // Crea cuadro de controles de texturizado
                                var controls = $("#" + viewer3DControls);
                                controls.append("<h4>" + data.name + "</h4>");
                                controls.append("<p class=\"description\">" + data.description + "</p>");
                                controls.append("<p>" + data.price + "</p>");

                                if (texturizable.length > 1) {
                                    $.each(texturizable, function (segmentid, texture) {
                                        if (texture.material.length > 1) {
                                            controls.append("<h4>" + texture.name + "</h4>");

                                            $.each(texture.material, function (textureid, material) {
                                            var btn = $("<button class=\"btn color\" data-id=\"" + id + "\" data-segment=\"" + segmentid + "\" data-texture=\"" + textureid + "\">&nbsp;</button>").css("background-color", material.color);
                                            $(btn).on('click', ChangeTexture)
                                            controls.append(btn);
                                            });
                                        }
                                    })
                                }
                            }

                            //--------------------------------------------------
                            //   Aplica texturas default al modelo
                            //--------------------------------------------------
                            function ApplyTextures(object, url, texturizable) {
                                var i = 0, j = 0;

                                //var material = new THREE.MeshBasicMaterial({ map: texture });
                                object.traverse(function (child) {
                                    var sprite = url + texturizable[i].material[j].sprite;
                                    var texture = new THREE.TextureLoader().load(sprite);

                                    if (child.isMesh) {
                                        child.castShadow = true;
                                        child.receiveShadow = true;
                                        child.material.map = texture
                                        child.material.needsUpdate = true;
                                        i++;
                                    }
                                });
                            }

                            //--------------------------------------------------
                            //   Handler de boton de cambio de texturas
                            //   Cambia la textura por la especificada en los
                            //   data tags
                            //--------------------------------------------------
                            function ChangeTexture() {
                                // Obtiene datos de objeto a modificar
                                var idObject = $(this).data('id');
                                var idSegment = $(this).data('segment');
                                var idtexture = $(this).data("texture");
                                var child = object3D.children[idSegment];

                                // Comppone nuevas texturas
                                var url = objectData[idObject].data.url;
                                var texturizable = objectData[idObject].texturizable;
                                var sprite = url + texturizable[idSegment].material[idtexture].sprite;
                                var texture = new THREE.TextureLoader().load(sprite);

                                // Aplica nueva textura
                                    if (child.isMesh) {
                                    child.castShadow = true;
                                    child.receiveShadow = true;
                                    child.material.map = texture
                                    child.material.needsUpdate = true;
                                }
                            }

                            //--------------------------------------------------
                            //   Crea Carrusel de preview de modelos
                            //--------------------------------------------------
                            function LoadPreviews(objectData) {
                                // Crea controles de seleccion de otros objetos
                                if (objectData.length > 1) {
                                    var selector = $("#" + viewer3DSelector);
                                    $.each(objectData, function (index, obj) {
                                        var item = $("<div data-id=\"" + index + "\"></div>");
                                        item.append("<h4>" + obj.data.name + "</h4>");
                                        item.append("<img src=\"placeholder.png\"  class=\"preview\" alt=\"\" \/>");
                                        item.append("<p>" + obj.data.description + "</p>");
                                        $(item).on('click', ChangeModel)

                                        selector.append(item);
                                    })
                                }
                            }

                            //--------------------------------------------------
                            //   Carga modelo especificado
                            //--------------------------------------------------
                            function ChangeModel() {
                                var idObject = $(this).data('id');

                                scene.remove(object3D);
                                $("#" + viewer3DControls).html("");
                                LoadModel(objectData, idObject);
                            }
                        </script>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="contenedorConf">
                            <img src="../img/Front/Logos/trraLogo.png" alt="" class="LogoConf">
                        </div>
                        <div class="contenedorConf">
                            <h4 class="textConfigurador">Modelo</h4>
                        </div>
                        <div class="contenedorConf">
                            <h4 class="MaterialesConfigurador">Materiales</h4>
                        </div>
                        <h4 class="MaterialesConfiguradorDesp">Cubierta</h4>
                        <div class="contenedorMateriales">
                            <div class="row">
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="loadTextures(0)" src="../img/Front/Texturas/cojin1.jpg" alt="Cotuffet">
                                </div>
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="loadTextures(1)" src="../img/Front/Texturas/cojin2.jpg" alt="Cotuffet">
                                </div>
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="loadTextures(1)" src="../img/Front/Texturas/cojin3.jpg" alt="Cotuffet">
                                </div>
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="" src="../img/Front/Texturas/cojin4.jpg" alt="Cotuffet">
                                </div>
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="" src="../img/Front/Texturas/cojin5.jpg" alt="Cotuffet">
                                </div>
                            </div>
                        </div>
                        <h4 class="MaterialesConfiguradorDesp">Estructura</h4>
                        <div class="contenedorMateriales">
                            <div class="row">
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="loadTextures(0)" src="../img/Front/Texturas/cojin1.jpg" alt="Cotuffet">
                                </div>
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="loadTextures(1)" src="../img/Front/Texturas/cojin2.jpg" alt="Cotuffet">
                                </div>
                                <div class="col-2">
                                    <img class="texturasDesp" onclick="loadTextures(1)" src="../img/Front/Texturas/cojin3.jpg" alt="Cotuffet">
                                </div>
                            </div>
                        </div>
                        <div class="container contIconComp">
                            <div class="row">
                                <div class="col-4 col-lg-4">
                                    <center><img class=""  src="../img/Front/Iconos/i_pdf.png" alt="Cotuffet"></center>
                                </div>
                                <div class="col-4 col-lg-4">
                                    <center><img class=""  src="../img/Front/Iconos/i_img.png" alt="Cotuffet"></center>
                                </div>
                                <div class="col-4 col-lg-4">
                                    <center><img class=""  src="../img/Front/Iconos/i_comp.png" alt="Cotuffet"></center>
                                </div>
                            </div>
                        </div>
                        <script>
                            var coll = document.getElementsByClassName("MaterialesConfiguradorDesp");
                            var i;
                            for (i = 0; i < coll.length; i++) {
                                coll[i].addEventListener("click", function iniciar() {
                                    this.classList.toggle("active");
                                    var content = this.nextElementSibling;
                                    if (content.style.maxHeight){
                                        content.style.maxHeight = null;
                                    }
                                    else {
                                        content.style.maxHeight = content.scrollHeight + "px";
                                    }
                                });
                            }
                        </script>
                    </div>
                </div>
              </div> <!-- Fin del Configurador -->
              <div class="col-12 col-md-4">
                  <div class="envolturaInicial" style="position: relative; text-align: center; top: 140px; border-radius: 4px; padding: 15px;">
                        <img src="../img/Front/Iconos/mueble.png" alt="" style="position: relative; left: -10%; width: 13%; height: 13%;"> 
                        <a href="{{ route('PaginaProductos') }}" style="font-size: 150%; margin-bottom: 0; color: black; text-decoration: none; position: relative; top: 2%;">Visita nuestra tienda</a>
                  </div>
                  <div class="envolturaInicial" style="position: relative; text-align: center; top: 160px; border-radius: 4px; padding: 15px;">
                        <img src="../img/Front/Iconos/tactil.png" alt="" style="position: relative; left: -10%; width: 13%; height: 13%;"> 
                        <a href="{{ route('PaginaProductos') }}" style="font-size: 150%; margin-bottom: 0; color: black; text-decoration: none; position: relative; top: 2%;">Decora desde tu celular</a>
                  </div>
              </div>
              <div class="contenedorScroll">
                <div class="scroll no-scroll autoplay" id="contenidoScroll">
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/mesa1.png" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/mesa2.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/mueble1_c.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/mueble2_c.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/mueble3_c.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/mueble4_p2.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/silla_p1.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/sillon1.jpg" alt="" class="envolturaInicial">
                </div>
                <div class="tarjeta">
                    <h1 class="envolturaInicial">Nombre</h1>
                    <img src="../img/Front/Muebles/sillon2.jpg" alt="" class="envolturaInicial">
                </div>
                </div>
            </div>
          </div>
      </section>
      <section class="container-fluid" id="contenedor2">
          <div class="row" style="border-top: 1px solid BCB07D;">
                <div class="col-12 col-lg-4 contTextSeccion1">
                    <h1>CONTÁCTANOS:</h1>
                    <label class="contSeccion">
                        <img src="../img/Front/Iconos/ubicacionseleccion.png" alt="" class="iconSeccion">
                        Hamburgo 33, Cuauhtemoc, <br> 06600 Ciudad de México, CDMX
                    </label><br>
                    <label class="contSeccion">
                        <img src="../img/Front/Iconos/telefonoseleccion.png" alt="" class="iconSeccion">
                        55 1023 8634
                    </label><br>
                    <label class="contSeccion">
                        <img src="../img/Front/Iconos/emailseleccion.png" alt="" class="iconSeccion">
                        hola@cotuffet.com
                    </label>
                </div>
                <div class="col-12 col-lg-7">
                    <br><br><br><br><iframe class="mapa" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3762.623236036175!2d-99.16231754938902!3d19.428678245775902!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1ff33af70fda1%3A0x3595ae2a524720e9!2zSGFtYnVyZ28gMzMsIEp1w6FyZXosIEN1YXVodMOpbW9jLCAwNjYwMCBKdcOhcmV6LCBDRE1Y!5e0!3m2!1ses!2smx!4v1579354466287!5m2!1ses!2smx" width="100%" height="500" frameborder="0" allowfullscreen=""></iframe><br>
                </div>
          </div>
      </section>
@endsection
