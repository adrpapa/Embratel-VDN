#!/usr/bin/env python3 
# Chromedriver http://chromedriver.storage.googleapis.com/index.html?path=2.14/
# pip3 install selenium
# install phantomjs from software.opensuse.org/packages/phantomjs


import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait 
from selenium.webdriver.support import expected_conditions as EC



def login(name, pwd):
    gotoFrame("loginFrame")
    getById("inp_user").send_keys(name)
    getById("inp_password").send_keys(pwd)
    getById("login").click()

def gotoFrame(frame):
    driver.switch_to_default_content()
    driver.switch_to_frame(frame)
    
def goToApplicationPackageList():
    gotoFrame("leftFrame")
    getById("item_aps_applications").click()
    gotoFrame("mainFrame")

def goToServiceTemplates():
    gotoFrame("leftFrame")
    getById("item_service_templates").click()
    gotoFrame("mainFrame")

def goToApsPackageList():
    goToApplicationPackageList()
    getById("first-tab").click()

def goToApsPackage(name):
    goToApsPackageList()
    clickOnGlobalList(1)

def goToResourceTypes():
    goToApsPackage('VDN_Embratel')
    getById("apsapp_resources").click()
    
def getTitle():
    els = driver.find_elements_by_id("title")
    return els[0].text if len(els) > 0 else ""



def getByIdNowait(id):
    try:
        return driver.find_element_by_id(id);
    except:
        return None

def getById(id):
    return WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, id)))


def createResourceType():
    createBtn=getByIdNowait("aps_packages_manage")
    if createBtn is None:
        goToResourceTypes()
        createBtn=getById("aps_packages_manage")
    createBtn.click()

def clickOnGlobalList(seq):
    gl=WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "global_list")))
    while len(gl.find_elements_by_tag_name("tr")) < seq:
        trs=WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "tr")))
    gl.find_elements_by_tag_name("tr")[seq].find_element_by_tag_name("span").click()

def clickOnGlobalListName(name):
    for span in getById("global_list").find_elements_by_tag_name("span"):
        if span.text == name:
            span.click()
            return True
    return False

def createAppService(service, autoprovision=False):
    createResourceType()
    clickOnGlobalList(1)
    selectResourceService(service, autoprovision)

def createAppReference(service):
    if getTitle() != 'Add New Resource Type':
        createResourceType()
    clickOnGlobalList(2)
    assert 'Add New Resource Type' in getTitle()
    setResourceName(service)
    #clica no nome do APS type
    clickOnGlobalList(1)
    clickOnGlobalList(1)
    submitForm()

def submitForm():
    driver.find_element_by_class_name("ButtonSubmit").click()

    
def selectResourceService(service, autoprovision):
    setResourceName(service)
    if not clickOnGlobalListName(service):
        raise Exception("Service "+service+" Not founfd in list")
    if autoprovision:
        getById("check_rt_autoprovision").click()
    submitForm()
    submitForm()
    return

def setResourceName(name, id="inp_rt_name"):
    getById(id).send_keys(name)
    submitForm()

def createServiceTemplate(name, autoprovisioning, services):
    goToServiceTemplates()
    getById("service_templates_create").click()
    getById("check_template_autoprovided").click()
    setResourceName(name, id="inp_template_name")
    selectServiceTemplateResource(services)
    submitForm()
    submitForm()
    submitForm()

def selectServiceTemplateResource(services):
    for row in getById("global_list").find_elements_by_tag_name("tr"):
        cells = row.find_elements_by_tag_name("td")
        if len(cells) > 1 and cells[1].text in services:
            cells[0].click()
    return False

def activateAndSubscribe(name):
    goToServiceTemplates()
    clickOnGlobalListName(name)
    # Activate subscription
    if driver.find_elements_by_class_name("s-btn")[3].text == 'Activate':
        driver.find_elements_by_class_name("s-btn")[3].click()
    # go to subscriptions tab
    driver.find_element_by_id('subscriptions').click()
    # create new subscription
    driver.find_element_by_id('subscriptions_create').click()
    # give the first customer a brand new subscription!
    clickOnGlobalList(1)
    submitForm()
    driver.find_element_by_class_name("action").click()

#def deleteAllResources():

if True:
    driver=webdriver.Chrome('/chromedriver/chromedriver')

    try:
        driver.get("http://cdn.flts.apsdemo.org:8080/")
        assert 'Parallels® Automation' in driver.title
        login('admin','123@mudar')

        services=['VDN Embratel globals', 'VDN Embratel Management', 'VDN Live Channels', 'VDN Virtual Private Server', 'VDN Job Content']

        createAppReference(services[0])
        createAppService(services[1], True)
        createAppService(services[2])
        createAppService(services[3])
        createAppService(services[4])

        createServiceTemplate(services[0], True, services)
        activateAndSubscribe(services[0])

        # logout from admin
        gotoFrame("topFrame")
        driver.find_element_by_id("topTxtLogout").click()
        login('zedaesquina','123@mudar')
        input("waiting for your tests...")
    finally:
        driver.close()


#driver.switch_to_default_content()
#driver.switch_to_frame("leftFrame")
#driver.find_element_by_id("item_aps_applications").click()
#driver.switch_to_default_content()
#driver.switch_to_frame("mainFrame")

#applist=driver.find_elements_by_id("global_list")
#if len(applist) != 1:
    #raise Exception("Favor instalar o pacote e a instância via Eclipse")
    #exit

#applist[0].find_element_by_class_name("linkWrapper").click()

#driver.switch_to_default_content()
#driver.switch_to_frame("mainFrame")


#lista=driver.find_elements_by_id("apsapp_instances")
#lista[0].click()

##verifica status
#driver.switch_to_default_content()
#driver.switch_to_frame("mainFrame")
#driver.find_element_by_class_name("listContentLayout").find_element_by_class_name("linkWrapper").click()
#assert 'Ready' in driver.find_element_by_id("indicator").text

##Back to app list
#driver.back()

##Now, create resources...
#driver.switch_to_default_content()
#driver.switch_to_frame("mainFrame")


#def goToResourceTypes():
#driver.switch_to_default_content()
#driver.switch_to_frame("mainFrame")
#driver.find_elements_by_id("apsapp_resources")[0].click()


#entra na instância
#lista=driver.find_element_by_id("cp_core_aps_modules_screens_ApplicationPropsMult:MasterInstances:e_form")
#lista=lista.find_element_by_id("global_list")
#span=lista.find_element_by_class_name("linkWrapper")

#lista=driver.find_elements_by_id("apsapp_instances")
#lista[0].click()
