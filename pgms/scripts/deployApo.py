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

def uploadPackage(pkgName):
    goToApplicationPackageList()
    gotoFrame("mainFrame")
    driver.find_element_by_class_name("actions-box").find_element_by_tag_name("a").click()


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

def createResourceCounter(counter):
    createResourceType()
    clickOnGlobalList(3)
    selectResourceService(counter, False)

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
        raise Exception("Service "+service+" Not found in list")
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
	# select 100 on page
    driver.find_elements_by_class_name("linkWrapper")[16].click()
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
    
    ## go to subscriptions tab
    #driver.find_element_by_id('subscriptions').click()
    ## create new subscription
    #driver.find_element_by_id('subscriptions_create').click()
    ## give the first customer a brand new subscription!
    #clickOnGlobalList(1)
    #submitForm()
    #driver.find_element_by_class_name("action").click()

def createServicePlan(name):
    gotoFrame("topFrame")
    driver.find_element_by_id("topTxtToBM") .click()
    
    gotoFrame("leftFrame")
    getById("click_service_plans").click()
    gotoFrame("mainFrame")
    getById("input___add").click()
    getById("ServicePlanType_4").click()
    submitForm()
    getById("input___name").send_keys(name)
    getById("input___shortDescription").send_keys(name)
    getById("input___longDescription").send_keys(name)
    getById("input___PlanCategoryplanCategoryID").send_keys("o")
    getById("input___refServiceTemplate").click()
    getById("input___vPublished").click()
    getById("input___BillingPeriod").send_keys("1")
    getById("input___RecurringType").send_keys("A")

    id=1
    while driver.find_element_by_class_name("wizard-item-current").text == "1\nConfigure Billing Terms":
        getById("input___ServiceTemplateserviceTemplateID").send_keys("\b\b\b\b\b\b"+str(id))
        submitForm()
        id += 1
    
    #Seleciona período de assinatura
    getById("vec_t1_7").click()
    submitForm()

    getById("input_____NextStep").click()

    #Configura valores
    getById("input___PlanRaterecurringFee-6").clear()
    getById("input___PlanRaterecurringFee-6").send_keys(750)
    getById("input___PlanRateincludedValue-6").clear()
    getById("input___PlanRateincludedValue-6").send_keys("1000000")
    getById("input___PlanRatecostForAdditional-6").clear()
    getById("input___PlanRatecostForAdditional-6").send_keys("0.90")
    getById("input___PlanRatecostForAdditional-7").clear()
    getById("input___PlanRatecostForAdditional-7").send_keys("0.05")
    getById("input___PlanRatemeasurable-6").click()
    getById("input___PlanRateIsMain-6").click()
    getById("input___PlanRateIsVisible-6").click()
    getById("input___PlanRatemeasurable-7").click()
    getById("input___PlanRateIsMain-7").click()
    getById("input___PlanRateIsVisible-7").click()
    submitForm()
    submitForm()


def createCategory():
    # Adicionar categoria à tela da loja
    gotoFrame("leftFrame")
    getById("click_products_online_store").click()
    gotoFrame("mainFrame")
    # screens
    getById("webgate__tab_5").click()
    # Screen APS Service
    clickOnGlobalList(5)
    driver.find_element_by_id("input___AddNewCategory").click()
    getById("input___Name").send_keys(name)
    getById("input___ShortDescription").send_keys(name)
    getById("input___DisplayByDefault").click()
    getById("input___ShowInCCP").click()
    submitForm()

	getById("vec_t1_1").click()
    submitForm()

#def deleteAllResources():

driver=webdriver.Chrome('/chromedriver/chromedriver')
driver.get("http://host1.apo.apsdemo.org:8080/")
login('admin','123@mudar')
if False:
    #try:
        try:
            driver.get("http://host1.apo.apsdemo.org:8080/")
            # driver.get("http://cdn.flts.apsdemo.org:8080/")
            instance="cdn"
        except:
            driver.get("http://host1.apo.apsdemo.org:8080/")
            instance="apo"
        assert 'Parallels® Automation' in driver.title
        login('admin','123@mudar')

        services=['VDN Embratel globals', 'VDN Embratel Management', 'VDN Live Channels', 'VDN Content', 'VDN Job', 'Content Delivery Network', 'httpTrafficInGB', 'http_s_TrafficInGB']

        createAppReference(services[0])
        createAppService(services[1], True)
        createAppService(services[2])
        createAppService(services[3])
        createAppService(services[4])
        createAppService(services[5])
        createResourceCounter(services[6])
        createResourceCounter(services[7])

        createServiceTemplate(services[0], True, services)
        activateAndSubscribe(services[0])
        gotoPBA()
        
        ## logout from admin
        #gotoFrame("topFrame")
        #driver.find_element_by_id("topTxtLogout").click()
        #if instance == "cdn":
            #login('zedaesquina','123@mudar')
        #else:
            #login('adrpapa','123@mudar')
        #input("waiting for your tests...")
    #finally:
        #driver.close()


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
