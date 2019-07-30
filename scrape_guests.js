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
  elements.forEach(function (element) {
    utils.dump(element);
    // casper.echo(element);
    if (element["x"] < 1003) {
      z=element["text"];
      if (element["x"] == 104) person["name"] = z.substring(1, z.length-1);
      if (element["x"] == 318) person["company"] = z.substring(1, z.length-1);
      if (element["x"] == 386) person["phone"] = z.substring(1, z.length-1);
      if (element["x"] == 466) person["email"] = z.substring(1, z.length-1);
      if (element["x"] == 656) person["business_sector"] = z.substring(1, z.length-1);
      if (element["x"] == 757) person["specialty"] = z.substring(1, z.length-1);
      if (element["x"] == 868) person["visit_date"] = z.replace(/\./g,'/').substring(1, z.length-1);
      if (element["x"] == 1002) {
        person["invited_by"] =  z.substring(1, z.length-1);
        people.push(JSON.stringify(person));
        // utils.dump(person);
        // fs.writeFileSync('./data.json', util.inspect(obj) , 'utf-8');
        // casper.echo("--------------------------------------------------------------------------")
      }
      
    }
  });
  fs.write(id+"guests.json", "[", 'w');
  fs.write(id+"guests.json", people, 'w+');
  fs.write(id+"guests.json", "]", 'w+');
  // utils.dump(people);
  this.echo("Everything done");
  this.exit();
});