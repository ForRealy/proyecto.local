from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import csv

def scrape_pokemon_images(url):
    # Configurar Selenium con el controlador de Chrome
    service = Service(executable_path="/usr/bin/chromedriver")  # Cambia por la ruta de tu chromedriver
    options = webdriver.ChromeOptions()
    options.headless = False  # Cambiar a True para modo headless

    driver = webdriver.Chrome(service=service, options=options)

    try:
        # Navegar a la página web
        driver.get(url)

        # Esperar a que se cargue la tabla
        wait = WebDriverWait(driver, 30)  # Aumentar tiempo de espera
        wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, 'table#pokedex')))

        # Crear una lista para almacenar los datos
        pokemon_data = []

        # Procesar cada fila
        while True:
            try:
                # Reobtener las filas dinámicamente en cada iteración
                rows = driver.find_elements(By.CSS_SELECTOR, 'table#pokedex tbody tr')

                for row in rows:
                    try:
                        # Volver a localizar columnas dinámicamente
                        columns = row.find_elements(By.TAG_NAME, 'td')
                        if columns:
                            number = columns[0].text
                            name = columns[1].text
                            type_ = columns[2].text
                            
                            print(f"Procesando: {name} ({number})")  # Registro del progreso

                            # Encontrar el enlace al Pokémon
                            link_element = columns[1].find_element(By.TAG_NAME, 'a')
                            pokemon_link = link_element.get_attribute('href')

                            # Navegar a la página del Pokémon
                            driver.get(pokemon_link)

                            # Esperar a que se cargue la imagen
                            wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, 'a[rel="lightbox"]')))

                            # Obtener la URL de la imagen
                            image_element = driver.find_element(By.CSS_SELECTOR, 'a[rel="lightbox"]')
                            image_url = image_element.get_attribute('href')

                            # Volver a la página principal
                            driver.back()
                            wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, 'table#pokedex')))

                            # Añadir datos a la lista
                            pokemon_data.append([number, name, type_, image_url])

                            # Añadir un retraso
                            time.sleep(2)  # Evitar sobrecarga del servidor
                    except Exception as e:
                        print(f"Error al procesar una fila: {e}")

                # Salir del bucle si no hay más filas
                break
            except Exception as e:
                print(f"Error al volver a procesar las filas: {e}")
                time.sleep(2)  # Pequeño retraso antes de intentar de nuevo

        # Guardar datos en un archivo CSV
        with open('pokemon_images.csv', 'w', newline='', encoding='utf-8') as file:
            writer = csv.writer(file)
            # Escribir encabezados
            writer.writerow(['Number', 'Name', 'Type', 'Image URL'])
            # Escribir filas
            writer.writerows(pokemon_data)

        print("Datos extraídos y guardados en 'pokemon_images.csv'.")

    except Exception as e:
        print(f"Ocurrió un error: {e}")
    finally:
        # Cerrar el navegador
        driver.quit()

# URL de la página
pokemon_db_url = "https://pokemondb.net/pokedex/all"
scrape_pokemon_images(pokemon_db_url)
