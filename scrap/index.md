import selenium
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
import time

#URL of CNN's homepage
cnn_url = "https://www.cactuseros.com/GenerosEspeciesIdentificadas/A.html"

#Function to scrape headlines using Selenium
def scrape_with_selenium(url):


    #print("Selenium version:", selenium.__version__)


  #  options = Options()
    
    
    options = webdriver.ChromeOptions()
    options.headless = False  # Set to True for headless mode
    
  #  options.binary_location = "/usr/bin/chromium-browser"
  #  chrome_driver_binary = "/usr/bin/chromedriver"
  #  driver = webdriver.Chrome(chrome_driver_binary, chrome_options=options)
    

#    

    service = Service(executable_path="/usr/bin/chromedriver")
    options = webdriver.ChromeOptions()
    driver = webdriver.Chrome(service=service, options=options)
# ...
#driver.quit()
    
    
   # driver = webdriver.Chrome(options=options)

    #Navigate to the webpage
    driver.get(url)

    #Interact with the webpage using Selenium
    # Example: Click on a button that loads more articles

    species_list = driver.find_element(By.CLASS_NAME, 'speciesList')
    
    all_species = species_list.find_elements(By.CLASS_NAME, 'speciesNewRow')

    for specie in all_species:
        print(specie.find_element(By.CLASS_NAME, 'speciesTitle').text)
        print(specie.find_element(By.CLASS_NAME, 'speciesTitle').get_attribute('href'))


    #cookies_button = driver.find_element(By.ID, 'onetrust-accept-btn-handler')
    #cookies_button.click();

    ## no_of_jobs = int(wd.find_element(By.CSS_SELECTOR, 'h1>span'))
    ## load_more_button = driver.find_element_by_css_selector('.load-more-button')
    ## load_more_button = driver.find_element(By.CSS_SELECTOR, '.load-more-button')
    ## load_more_button.click()

    #Allow time for dynamic content to load (you may need to use WebDriverWait for more robust waiting)
    #time.sleep(3)

    #Extract and print headlines after loading more content
    ## headlines = driver.find_elements_by_css_selector('.card h3')
    #headlines = driver.find_elements(By.TAG_NAME, 'h2')
    #for headline in headlines:
    #    print(headline.text)

    #Close the browser window
    driver.quit()

#Scrape headlines using Selenium
scrape_with_selenium(cnn_url)