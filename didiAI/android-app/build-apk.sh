#!/bin/bash
# didi AI Android APK 一条龙构建：SDK 安装 + Gradle 构建 + 产物复制
set -e
export JAVA_HOME=/usr/lib/jvm/java-17-openjdk-amd64
export ANDROID_HOME=/opt/android-sdk
mkdir -p "$ANDROID_HOME"

echo "=== 1/4 Android cmdline-tools ==="
if [ ! -d "$ANDROID_HOME/cmdline-tools/latest" ]; then
  curl -sL -o /tmp/opencode/cmdtools.zip https://dl.google.com/android/repository/commandlinetools-linux-11076708_latest.zip
  unzip -qo /tmp/opencode/cmdtools.zip -d /tmp/opencode/cmdt
  mkdir -p "$ANDROID_HOME/cmdline-tools"
  mv /tmp/opencode/cmdt/cmdline-tools "$ANDROID_HOME/cmdline-tools/latest"
fi

echo "=== 2/4 SDK components ==="
yes | "$ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager" --licenses > /dev/null 2>&1 || true
"$ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager" "platform-tools" "build-tools;34.0.0" "platforms;android-34" 2>&1 | tail -2

echo "=== 3/4 Gradle 8.7 ==="
if [ ! -d /opt/gradle-8.7 ]; then
  curl -sL -o /tmp/opencode/gradle.zip https://services.gradle.org/distributions/gradle-8.7-bin.zip
  unzip -qo /tmp/opencode/gradle.zip -d /opt/
fi

echo "=== 4/4 Build APK ==="
cd /workspace/didiAI/android-app
/opt/gradle-8.7/bin/gradle assembleRelease --no-daemon -q 2>&1 | tail -15
mkdir -p /workspace/didiAI/wp-content/uploads/apps
cp app/build/outputs/apk/release/app-release.apk /workspace/didiAI/wp-content/uploads/apps/didi-ai.apk
ls -la /workspace/didiAI/wp-content/uploads/apps/
echo "=== BUILD OK ==="
