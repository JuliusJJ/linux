var casper = require('casper').create({
  viewportSize: { width: 1280, height: 800 },
  loadImages: true,
  loadPlugins: true,
});
// var url = 'https://www.bnisantaka.com/';
var url = 'https://www.bniconnectglobal.com/web/secure/reportsHome';

// casper.userAgent('');
var html = "";
var system = require("system");
var args = system.args;
var name = args[4];
var psw = args[5];
var id = args[6];
casper.start(url, function () {
  this.echo("started");
  this.echo("1/4");
  casper.wait(10000, function () {
    this.fillSelectors('form.hJcysH', {
      'input[name="username"]': name,
      'input[name="password"]': psw,
    }, true);
  });
  casper.then(function () {
    this.echo("2/4");
    casper.wait(10000, function () {
      this.click('a[href="#ui-tabs-19"]');
    })
  })

  casper.then(function () {
    this.echo("3/4");
    casper.wait(10000, function () {
      // this.click('input[value="Pateikti"]');
      this.fillSelectors('form#chapter_Prospective_Visitor', {}, true);
    });
  })

  casper.then(function () {
    this.echo("4/4");
    casper.wait(10000, function () {
      this.capture("t.png");
      this.echo("pic taken")
      this.page.switchToChildFrame(0);
      casper.wait(3000, function () {
        elements = casper.getElementsInfo('#__bookmark_1 tbody td');
      })
      casper.wait(3000, function () {
        this.page.switchToParentFrame();
      })

    })
  })

  // var js = this.evaluate(function() {
  // 	return document; 
  // });	
  // casper.then(function(){
  //   this.echo(js.all[0].outerHTML); 
  // })

});

casper.run(function () {
  var utils = require('utils');
  var fs = require('fs');
  // utils.dump(elements);
  
  var person = {};
  var people = [];
  var i = 0;
  elements.forEach(function (element) {
    console.log(i);
    utils.dump(element);
    // casper.echo(element);

    z=element["text"];
    if (i==1) person["name"] = z.substring(1, z.length-1);
    if (i==2) person["company"] = z.substring(1, z.length-1);
    if (i==3) person["phone"] = z.substring(1, z.length-1);
    if (i==4) person["email"] = z.substring(1, z.length-1);
    if (i==5) person["business_sector"] = z.substring(1, z.length-1);
    if (i==6) person["specialty"] = z.substring(1, z.length-1);
    if (i==7) person["visit_date"] = z.replace(/\./g,'/').substring(1, z.length-1);
    if (i==8) {
      person["invited_by"] =  z.substring(1, z.length-1);
      people.push(JSON.stringify(person));
    }
    if(i==9) i=0;
    else i++;

    // if (element["x"] < 1100) {
    //   z=element["text"];
    //   if (element["x"] >100 && element["x"] <200) person["name"] = z.substring(1, z.length-1);
    //   if (element["x"] >300 && element["x"] <350) person["company"] = z.substring(1, z.length-1);
    //   if (element["x"] >350 && element["x"] <450) person["phone"] = z.substring(1, z.length-1);
    //   if (element["x"] >450 && element["x"] <550) person["email"] = z.substring(1, z.length-1);
    //   if (element["x"] >550 && element["x"] <750) person["business_sector"] = z.substring(1, z.length-1);
    //   if (element["x"] >750 && element["x"] <850) person["specialty"] = z.substring(1, z.length-1);
    //   if (element["x"] >850 && element["x"] <950) person["visit_date"] = z.replace(/\./g,'/').substring(1, z.length-1);
    //   if (element["x"] >950 && element["x"] <1100) {
    //     person["invited_by"] =  z.substring(1, z.length-1);
    //     people.push(JSON.stringify(person));
    //   }
    // }
  });
  fs.write(id+"guests.json", "[", 'w');
  fs.write(id+"guests.json", people, 'w+');
  fs.write(id+"guests.json", "]", 'w+');
  // utils.dump(people);
  this.echo("Everything done");
  this.exit();
});